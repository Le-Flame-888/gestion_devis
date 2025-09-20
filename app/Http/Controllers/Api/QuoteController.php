<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function index()
    {
        return Quote::with(['client', 'user', 'details.product'])->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'date_devis' => 'required|date',
            'date_validite' => 'required|date|after:date_devis',
            'tva' => 'nullable|numeric|min:0|max:100',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantite' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        return DB::transaction(function () use ($request) {
            $quoteData = [
                'numero_devis' => $this->generateQuoteNumber(),
                'client_id' => $request->client_id,
                'user_id' => auth()->id(),
                'date_devis' => $request->date_devis,
                'date_validite' => $request->date_validite,
            ];
            
            // Set default TVA to 20% if not provided
            $quoteData['tva'] = $request->input('tva', 20.00);
            
            $quote = Quote::create($quoteData);

            $this->addProductsToQuote($quote, $request->products);
            $this->calculateQuoteTotals($quote);

            return response()->json($quote->load(['client', 'user', 'details.product']), 201);
        });
    }

    public function show(Quote $quote)
    {
        return $quote->load(['client', 'user', 'details.product']);
    }

    public function update(Request $request, Quote $quote)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'sometimes|required|exists:clients,id',
            'date_devis' => 'sometimes|required|date',
            'date_validite' => 'sometimes|required|date|after:date_devis',
            'statut' => 'sometimes|required|in:brouillon,envoye,accepte,refuse',
            'tva' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Only update fields that are present in the request
        $updateData = $request->only(['client_id', 'date_devis', 'date_validite', 'statut']);
        
        // Only include TVA if it's provided in the request
        if ($request->has('tva')) {
            $updateData['tva'] = $request->tva;
            $this->calculateQuoteTotals($quote);
        }
        
        $quote->update($updateData);

        return response()->json($quote->load(['client', 'user', 'details.product']));
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return response()->json(null, 204);
    }

    public function addProduct(Request $request, Quote $quote)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantite' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::findOrFail($request->product_id);
        
        $detail = QuoteDetail::create([
            'quote_id' => $quote->id,
            'product_id' => $product->id,
            'quantite' => $request->quantite,
            'prix_unitaire' => $product->prix_unitaire,
            'total_ligne' => $request->quantite * $product->prix_unitaire,
        ]);

        $this->calculateQuoteTotals($quote);

        return response()->json($detail->load('product'), 201);
    }

    public function removeProduct(Quote $quote, QuoteDetail $detail)
    {
        if ($detail->quote_id !== $quote->id) {
            return response()->json(['error' => 'Product not found in this quote'], 404);
        }

        $detail->delete();
        $this->calculateQuoteTotals($quote);

        return response()->json(null, 204);
    }

    private function generateQuoteNumber()
    {
        $year = date('Y');
        $lastQuote = Quote::whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->first();

        $number = $lastQuote ? 
            (int) substr($lastQuote->numero_devis, -4) + 1 : 1;

        return sprintf('DEV-%s-%04d', $year, $number);
    }

    private function addProductsToQuote(Quote $quote, array $products)
    {
        foreach ($products as $productData) {
            $product = Product::findOrFail($productData['product_id']);
            
            QuoteDetail::create([
                'quote_id' => $quote->id,
                'product_id' => $product->id,
                'quantite' => $productData['quantite'],
                'prix_unitaire' => $product->prix_vente,
                'total_ligne' => $productData['quantite'] * $product->prix_vente,
            ]);
        }
    }

    private function calculateQuoteTotals(Quote $quote)
    {
        // Log pour débogage
        \Log::info('Calcul des totaux pour le devis #' . $quote->id);
        \Log::info('TVA actuelle: ' . $quote->tva);
        
        $totalHT = $quote->details()->sum('total_ligne');
        \Log::info('Total HT calculé: ' . $totalHT);
        
        $totalTTC = $totalHT * (1 + $quote->tva / 100);
        \Log::info('Total TTC calculé: ' . $totalTTC);

        $updated = $quote->update([
            'total_ht' => $totalHT,
            'total_ttc' => $totalTTC,
        ]);
        
        \Log::info('Mise à jour des totaux: ' . ($updated ? 'succès' : 'échec'));
        \Log::info('Nouveaux totaux - HT: ' . $quote->total_ht . ', TTC: ' . $quote->total_ttc);
    }
}
