# Laravel Quote Management API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
This API uses Laravel Sanctum for authentication. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your-token}
```

## Test Accounts
- **Admin**: admin@example.com / password
- **User**: user@example.com / password

## Endpoints

### Authentication
- `POST /register` - Register a new user
- `POST /login` - Login and get access token
- `POST /logout` - Logout (requires auth)
- `GET /user` - Get current user info (requires auth)

### Products (requires auth)
- `GET /products` - List all products (paginated)
- `POST /products` - Create a new product
- `GET /products/{id}` - Get specific product
- `PUT /products/{id}` - Update product
- `DELETE /products/{id}` - Delete product

### Clients (requires auth)
- `GET /clients` - List all clients (paginated)
- `POST /clients` - Create a new client
- `GET /clients/{id}` - Get specific client
- `PUT /clients/{id}` - Update client
- `DELETE /clients/{id}` - Delete client

### Quotes (requires auth)
- `GET /quotes` - List all quotes (paginated)
- `POST /quotes` - Create a new quote
- `GET /quotes/{id}` - Get specific quote
- `PUT /quotes/{id}` - Update quote
- `DELETE /quotes/{id}` - Delete quote
- `POST /quotes/{id}/products` - Add product to quote
- `DELETE /quotes/{id}/products/{detail_id}` - Remove product from quote

## Example Usage

### 1. Register/Login
```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password","password_confirmation":"password"}'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

### 2. Create a Product
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{"nom":"Carrelage","description":"Carrelage 30x30","prix_unitaire":25.50,"unite":"m2","stock":100}'
```

### 3. Create a Client
```bash
curl -X POST http://localhost:8000/api/clients \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{"nom":"ABC Company","email":"contact@abc.com","telephone":"0123456789","adresse":"123 Main St","ville":"Paris","code_postal":"75001"}'
```

### 4. Create a Quote
```bash
curl -X POST http://localhost:8000/api/quotes \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "date_devis": "2025-08-26",
    "date_validite": "2025-09-26",
    "tva": 20,
    "products": [
      {"product_id": 1, "quantite": 50},
      {"product_id": 2, "quantite": 25}
    ]
  }'
```

## Features
- ✅ User authentication with Laravel Sanctum
- ✅ CRUD operations for Products, Clients, and Quotes
- ✅ Automatic quote number generation (DEV-YYYY-NNNN format)
- ✅ Automatic calculation of quote totals (HT and TTC)
- ✅ Input validation and error handling
- ✅ Pagination for list endpoints
- ✅ Database relationships and eager loading
- ✅ Test data seeding

## Database Schema
- **Users**: id, name, email, password, role (admin/user)
- **Products**: id, nom, description, prix_unitaire, unite (m2/m3), stock
- **Clients**: id, nom, email, telephone, adresse, ville, code_postal
- **Quotes**: id, numero_devis, client_id, user_id, dates, statut, totals, tva
- **Quote Details**: id, quote_id, product_id, quantite, prix_unitaire, total_ligne

## Getting Started
1. Ensure your database is configured in `.env`
2. Run `php artisan serve` to start the development server
3. Use the test accounts or register new users to access the API
4. All API routes require authentication except register/login
