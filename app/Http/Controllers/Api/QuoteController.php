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
            $quote = Quote::create([
                'numero_devis' => $this->generateQuoteNumber(),
                'client_id' => $request->client_id,
                'user_id' => auth()->id(),
                'date_devis' => $request->date_devis,
                'date_validite' => $request->date_validite,
                'tva' => $request->tva ?? 20.00,
            ]);

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

        $quote->update($request->only(['client_id', 'date_devis', 'date_validite', 'statut', 'tva']));
        
        if ($request->has('tva')) {
            $this->calculateQuoteTotals($quote);
        }

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
                'prix_unitaire' => $product->prix_unitaire,
                'total_ligne' => $productData['quantite'] * $product->prix_unitaire,
            ]);
        }
    }

    private function calculateQuoteTotals(Quote $quote)
    {
        $totalHT = $quote->details()->sum('total_ligne');
        $totalTTC = $totalHT * (1 + $quote->tva / 100);

        $quote->update([
            'total_ht' => $totalHT,
            'total_ttc' => $totalTTC,
        ]);
    }
}
