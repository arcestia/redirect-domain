# Domain Randomizer API Documentation

## Overview
This API allows you to manage domain randomization rules, including source domains, target domains, and the relationships between them.

## Base URL
```
http://localhost:8080/api
```

## Authentication
Currently, the API does not implement authentication. It is recommended to implement appropriate authentication mechanisms before deploying to production.

## Endpoints

### Source Domains

#### List All Source Domains
```http
GET /sources
```

**Response**
```json
[
    {
        "id": 1,
        "domain": "domainA.com",
        "active": 1,
        "created_at": "2024-12-17T15:00:00+07:00",
        "updated_at": "2024-12-17T15:00:00+07:00"
    }
]
```

#### Add New Source Domain
```http
POST /sources
```

**Request Body**
```json
{
    "domain": "example.com"
}
```

**Response**
```json
{
    "id": 1,
    "domain": "example.com"
}
```

#### Delete Source Domain
```http
DELETE /sources/:id
```

**Parameters**
- `id`: Source Domain ID (in URL)

**Response**
```json
{
    "message": "Source domain deleted successfully"
}
```

**Error Responses**
```json
{
    "error": "Cannot delete source domain that is being used in rules. Delete associated rules first."
}
```
```json
{
    "error": "Source domain not found"
}
```

### Target Domains

#### List All Target Domains
```http
GET /targets
```

**Response**
```json
[
    {
        "id": 1,
        "domain": "targetA.com",
        "active": 1,
        "created_at": "2024-12-17T15:00:00+07:00",
        "updated_at": "2024-12-17T15:00:00+07:00"
    }
]
```

#### Add New Target Domain
```http
POST /targets
```

**Request Body**
```json
{
    "domain": "target.com"
}
```

**Response**
```json
{
    "id": 1,
    "domain": "target.com"
}
```

#### Delete Target Domain
```http
DELETE /targets/:id
```

**Parameters**
- `id`: Target Domain ID (in URL)

**Response**
```json
{
    "message": "Target domain deleted successfully"
}
```

**Error Responses**
```json
{
    "error": "Cannot delete target domain that is being used in rules. Delete associated rules first."
}
```
```json
{
    "error": "Target domain not found"
}
```

### Domain Rules

#### List All Rules
```http
GET /rules
```

**Response**
```json
[
    {
        "id": 1,
        "source_domain": "domainA.com",
        "target_domain": "targetA.com",
        "active": 1
    }
]
```

#### Add New Rule
```http
POST /rules
```

**Request Body**
```json
{
    "source_domain": "example.com",
    "target_domain": "target.com"
}
```

**Response**
```json
{
    "id": 1,
    "source_domain": "example.com",
    "target_domain": "target.com"
}
```

#### Toggle Rule Status
```http
PATCH /rules/:id
```

**Parameters**
- `id`: Rule ID (in URL)

**Request Body**
```json
{
    "active": true
}
```

**Response**
```json
{
    "id": 1,
    "active": true
}
```

#### Delete Rule
```http
DELETE /rules/:id
```

**Parameters**
- `id`: Rule ID (in URL)

**Response**
```json
{
    "message": "Rule deleted successfully"
}
```

## Error Responses

The API returns appropriate HTTP status codes and error messages:

### 400 Bad Request
Returned when the request is malformed or missing required fields.
```json
{
    "error": "Domain is required"
}
```

### 404 Not Found
Returned when the requested resource doesn't exist.
```json
{
    "error": "Source or target domain not found"
}
```

### 500 Internal Server Error
Returned when there's a server-side error.
```json
{
    "error": "Database error message"
}
```

## Example Usage

### Using cURL

1. Add a new source domain:
```bash
curl -X POST http://localhost:8080/api/sources \
  -H "Content-Type: application/json" \
  -d '{"domain": "newdomain.com"}'
```

2. Add a new target domain:
```bash
curl -X POST http://localhost:8080/api/targets \
  -H "Content-Type: application/json" \
  -d '{"domain": "newtarget.com"}'
```

3. Create a new rule:
```bash
curl -X POST http://localhost:8080/api/rules \
  -H "Content-Type: application/json" \
  -d '{
    "source_domain": "newdomain.com",
    "target_domain": "newtarget.com"
  }'
```

4. Toggle a rule's active status:
```bash
curl -X PATCH http://localhost:8080/api/rules/1 \
  -H "Content-Type: application/json" \
  -d '{"active": false}'
```

5. Delete a source domain:
```bash
curl -X DELETE http://localhost:8080/api/sources/1
```

6. Delete a target domain:
```bash
curl -X DELETE http://localhost:8080/api/targets/1
```

7. Delete a rule:
```bash
curl -X DELETE http://localhost:8080/api/rules/1
```

## Notes

1. All timestamps are in UTC+7 (Jakarta/Bangkok timezone)
2. Active status is represented as:
   - 1 or true: Active
   - 0 or false: Inactive
3. Domain names should be provided without protocol (http:// or https://)
4. The API automatically manages relationships between source and target domains
