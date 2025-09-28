# LexicalEntryApiController Documentation

The `LexicalEntryApiController` provides API endpoints for managing lexical entries in the Parf-Edhellen dictionary system. This controller handles retrieving, suggesting, and deleting lexical entries through RESTful API endpoints.

## Overview

The controller is part of the API v3 system and provides three main endpoints:
- **GET** - Retrieve a specific lexical entry by ID
- **POST** - Suggest lexical entries based on word input
- **DELETE** - Delete a lexical entry (admin only)

## API Endpoints

### Base URL
All endpoints are prefixed with `/api/v3/` where `v3` is the current API version.

### 1. Get Lexical Entry

**Endpoint:** `GET /api/v3/lexical-entry/{id}`

**Description:** Retrieves a specific lexical entry by its ID along with all related data.

**Parameters:**
- `id` (integer, required) - The ID of the lexical entry to retrieve

**Response:**
- **200 OK** - Returns the lexical entry data
- **404 Not Found** - If no lexical entry exists with the given ID

**Response Format:**
```json
{
    "lexical_entry": {
        "id": 123,
        "word": "example",
        "sense_id": 456,
        "language_id": 1,
        "account_id": 789,
        "is_deleted": false,
        "created_at": "2024-01-15T10:30:45.000Z",
        "updated_at": "2024-01-15T10:30:45.000Z",
        "account": {
            "id": 789,
            "nickname": "user123"
        },
        "sense": {
            "id": 456,
            "description": "example word",
            "word": {
                "id": 456,
                "word": "example"
            }
        },
        "speech": {
            "id": 1,
            "name": "noun"
        },
        "lexical_entry_group": {
            "id": 1,
            "name": "Basic Vocabulary"
        },
        "word": {
            "id": 123,
            "word": "example"
        },
        "glosses": [
            {
                "id": 1,
                "translation": "example translation",
                "lexical_entry_id": 123
            }
        ],
        "lexical_entry_details": [
            {
                "id": 1,
                "detail_type": "etymology",
                "content": "word origin information",
                "lexical_entry_id": 123
            }
        ]
    }
}
```

**Example Usage:**
```bash
curl -X GET "https://your-domain.com/api/v3/lexical-entry/123"
```

### 2. Suggest Lexical Entries

**Endpoint:** `POST /api/v3/lexical-entry/suggest`

**Description:** Suggests lexical entries based on an array of words. This endpoint performs intelligent matching including normalization and optional inexact matching.

**Request Body:**
```json
{
    "words": ["example", "test", "word"],
    "language_id": 1,
    "inexact": false
}
```

**Parameters:**
- `words` (array, required) - Array of words to find suggestions for
- `language_id` (integer, optional) - Language ID to filter results (0 for all languages)
- `inexact` (boolean, optional) - Enable inexact matching (default: false)

**Response:**
- **200 OK** - Returns grouped suggestions for each word

**Response Format:**
```json
{
    "example": [
        {
            "normalized_word": "example",
            "word": "example",
            "comments": "Additional comments",
            "type": "noun",
            "gloss": "example translation",
            "source": "Source reference",
            "account_name": "contributor123",
            "lexical_entry_group_name": "Basic Vocabulary",
            "id": 123
        }
    ],
    "test": [
        {
            "normalized_word": "test",
            "word": "test",
            "comments": null,
            "type": "verb",
            "gloss": "to test",
            "source": null,
            "account_name": "contributor456",
            "lexical_entry_group_name": "Actions",
            "id": 456
        }
    ],
    "word": []
}
```

**Algorithm Details:**
1. **Word Normalization:** All input words are converted to lowercase and normalized using UTF-8 encoding
2. **Duplicate Removal:** Duplicate words are automatically removed
3. **ASCII Normalization:** Words are normalized to ASCII form for database queries
4. **Inexact Matching:** When `inexact: true`, partial matches are performed using LIKE queries
5. **Direct Matching:** First attempts direct character-by-character matching
6. **Fallback Matching:** If no direct matches found, falls back to normalized matching

**Example Usage:**
```bash
curl -X POST "https://your-domain.com/api/v3/lexical-entry/suggest" \
  -H "Content-Type: application/json" \
  -d '{
    "words": ["hello", "world"],
    "language_id": 1,
    "inexact": false
  }'
```

### 3. Delete Lexical Entry (Admin Only)

**Endpoint:** `DELETE /api/v3/lexical-entry/{id}`

**Description:** Deletes a lexical entry. This endpoint requires administrator privileges and allows specifying a replacement lexical entry.

**Authentication:** Requires `auth`, `auth.require-role:Administrators`, and `verified` middleware.

**Parameters:**
- `id` (integer, required) - The ID of the lexical entry to delete

**Request Body:**
```json
{
    "replacement_id": 456
}
```

**Parameters:**
- `replacement_id` (integer, required) - ID of the lexical entry to use as replacement (must be different from the deleted entry)

**Response:**
- **200 OK** - Lexical entry successfully deleted
- **400 Bad Request** - Invalid replacement ID or deletion failed
- **401 Unauthorized** - Authentication required
- **403 Forbidden** - Administrator privileges required

**Validation Rules:**
- `replacement_id` must be numeric
- `replacement_id` cannot be the same as the lexical entry being deleted
- Replacement lexical entry must exist and have versions

**Example Usage:**
```bash
curl -X DELETE "https://your-domain.com/api/v3/lexical-entry/123" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "replacement_id": 456
  }'
```

## Data Models

### LexicalEntry
The main lexical entry model containing:
- **Basic Info:** ID, word, sense_id, language_id, account_id
- **Metadata:** Creation/update timestamps, deletion status
- **Relationships:** Account, sense, speech, lexical_entry_group, word
- **Collections:** Glosses, lexical_entry_details

### Gloss
Translation information for a lexical entry:
- **translation:** The translated text
- **lexical_entry_id:** Reference to parent lexical entry
- **Additional metadata:** Comments, source, etc.

### LexicalEntryDetail
Detailed information about a lexical entry:
- **detail_type:** Type of detail (e.g., "etymology", "notes")
- **content:** The detailed content
- **lexical_entry_id:** Reference to parent lexical entry

## Error Handling

### Common Error Responses

**404 Not Found:**
```json
{
    "message": "Not Found"
}
```

**400 Bad Request:**
```json
{
    "message": "Validation failed",
    "errors": {
        "replacement_id": ["The replacement id must not be 123."]
    }
}
```

**401 Unauthorized:**
```json
{
    "message": "Unauthenticated."
}
```

**403 Forbidden:**
```json
{
    "message": "This action is unauthorized."
}
```

## Implementation Details

### Repository Integration
The controller uses `LexicalEntryRepository` for all database operations:

- **`getLexicalEntry($id)`** - Retrieves lexical entry with all relationships
- **`suggest($words, $languageId, $inexact)`** - Performs intelligent word suggestion
- **`deleteLexicalEntryWithId($id, $replaceId)`** - Handles safe deletion with replacement

### Query Optimization
The suggest endpoint includes several optimizations:
- **Normalization caching** for repeated queries
- **Batch processing** for multiple words
- **Length-based ordering** for relevance
- **Limit controls** to prevent excessive results

### Security Considerations
- **Authentication required** for delete operations
- **Role-based access control** (administrators only for deletion)
- **Input validation** on all parameters
- **SQL injection protection** through Eloquent ORM

## Usage Examples

### Frontend Integration
```javascript
// Get a lexical entry
const getLexicalEntry = async (id) => {
    const response = await fetch(`/api/v3/lexical-entry/${id}`);
    return response.json();
};

// Suggest lexical entries
const suggestWords = async (words, languageId = 0, inexact = false) => {
    const response = await fetch('/api/v3/lexical-entry/suggest', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            words,
            language_id: languageId,
            inexact
        })
    });
    return response.json();
};

// Delete lexical entry (admin only)
const deleteLexicalEntry = async (id, replacementId) => {
    const response = await fetch(`/api/v3/lexical-entry/${id}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            replacement_id: replacementId
        })
    });
    return response.status === 200;
};
```

### Rate Limiting
All endpoints are subject to Laravel's default rate limiting. Consider implementing appropriate rate limits for production use, especially for the suggest endpoint which can be resource-intensive.

### Caching Recommendations
- **Lexical entry retrieval** can be cached for frequently accessed entries
- **Suggestion results** can be cached for common word combinations
- **Language-specific results** benefit from separate cache keys

## Related Documentation
- [Queue Job Statistics](./QUEUE_JOB_STATISTICS.md)
- [Expensive Requests Middleware](./EXPENSIVE_REQUESTS_MIDDLEWARE.md)
- [API Versioning Strategy](./API_VERSIONING.md)
