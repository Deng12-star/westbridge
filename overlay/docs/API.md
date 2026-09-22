# WestBridge API - v1

Base URL: `https://your-domain.com/api/v1`. All responses are JSON.
Limit: 60 requests per minute per client (`429` when exceeded).

## Public (no sign-in)

These return only what the website already shows.

| Method | Path | Returns |
|---|---|---|
| GET | `/site` | Company name, contact details and social links |
| GET | `/categories` | Visible shop categories with product counts |
| GET | `/products` | Published products, paginated. Query: `category` (slug), `q` (search), `per_page` (1-100) |
| GET | `/products/{slug}` | One product, with description and specifications |
| GET | `/projects` | Portfolio projects |
| GET | `/projects/{slug}` | One project |

`price` is `null` and `price_on_request` is `true` when no price is shown.
Every product includes `whatsapp_url`, the same pre-filled link the website
uses.

## Staff (token required)

Uses Laravel Sanctum personal access tokens.

```http
POST /api/v1/auth/token
Content-Type: application/json

{"email": "you@company.com", "password": "...", "device_name": "inventory-app"}
```

Returns `{"token": "1|abc...", "token_type": "Bearer", "expires_at": "..."}`.
Tokens last 90 days. Send the token on every call:

```http
Authorization: Bearer 1|abc...
```

| Method | Path | Permission |
|---|---|---|
| GET | `/me` | any staff |
| DELETE | `/auth/token` | revokes the token in use |
| POST | `/products` | `products.create` |
| PUT / PATCH | `/products/{id}` | `products.update` (PATCH updates only the fields sent) |
| DELETE | `/products/{id}` | `products.delete` |

Product fields: `name` (required on create), `slug`, `product_category_id`,
`sku`, `brand`, `short_description`, `description`, `price`, `currency`
(3 letters), `stock_status` (`in_stock`, `on_order`, `out_of_stock`),
`specs` (`[{"label": "...", "value": "..."}]`), `is_published`,
`is_featured`, `sort_order`. Photos are uploaded from the admin panel.

Errors: `401` no or bad token, `403` missing permission, `404` not found,
`422` validation (`{"message": ..., "errors": {field: [..]}}`), `429` too many
requests.
