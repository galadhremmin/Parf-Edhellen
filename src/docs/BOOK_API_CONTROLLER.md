# BookApiController Documentation

The `BookApiController` provides API endpoints for accessing and searching the Parf-Edhellen dictionary system. This controller handles retrieving lexical entries, searching words, and managing dictionary entities through RESTful API endpoints.

## Overview

The controller extends `BookBaseController` and provides endpoints for:
- **Dictionary Lookup** - Retrieve specific lexical entries and words
- **Search Functionality** - Find words and entities using various search criteria
- **Metadata Access** - Get languages, groups, and other reference data
- **Version Management** - Access historical versions of lexical entries

## API Endpoints

### Base URL
All endpoints are prefixed with `/api/v3/` where `v3` is the current API version.

## Public Endpoints (No Authentication Required)

### 1. Get Lexical Entry Groups

**Endpoint:** `GET /api/v3/book/group`

**Description:** Retrieves all available lexical entry groups (dictionary categories).

**Response:**
- **200 OK** - Returns array of lexical entry groups

**Response Format:**
```json
[
    {
        "id": 1,
        "name": "Basic Vocabulary",
        "label": "Basic",
        "is_canon": true,
        "is_old": false,
        "external_link_format": null,
        "created_at": "2024-01-15T10:30:45.000Z",
        "updated_at": "2024-01-15T10:30:45.000Z"
    }
]
```

### 2. Get Languages

**Endpoint:** `GET /api/v3/book/languages`

**Description:** Retrieves all available languages grouped by category. Results are cached for 1 month.

**Response:**
- **200 OK** - Returns languages grouped by category

**Response Format:**
```json
{
    "constructed": [
        {
            "id": 1,
            "name": "Sindarin",
            "order": 1,
            "category": "constructed"
        }
    ],
    "natural": [
        {
            "id": 2,
            "name": "English",
            "order": 1,
            "category": "natural"
        }
    ]
}
```

### 3. Get Lexical Entry by ID

**Endpoint:** `GET /api/v3/book/translate/{lexicalEntryId}`

**Description:** Retrieves a specific lexical entry with all related data adapted for display.

**Parameters:**
- `lexicalEntryId` (integer, required) - The ID of the lexical entry to retrieve

**Response:**
- **200 OK** - Returns the lexical entry data
- **404 Not Found** - If no lexical entry exists with the given ID

**Response Format:**
```json
{
    "id": 123,
    "word": "example",
    "translation": "example translation",
    "etymology": "word origin",
    "type": "noun",
    "source": "Source reference",
    "comments": "Additional notes",
    "tengwar": "tengwar_script",
    "language_id": 1,
    "account_name": "contributor123",
    "created_at": "2024-01-15T10:30:45.000Z",
    "updated_at": "2024-01-15T10:30:45.000Z",
    "lexical_entry_group_name": "Basic Vocabulary",
    "is_canon": true,
    "is_uncertain": false,
    "is_rejected": false,
    "external_id": "ext_123",
    "sense_id": 456,
    "label": "example_label",
    "latest_lexical_entry_version_id": 789
}
```

### 4. Get Lexical Entry from Version

**Endpoint:** `GET /api/v3/book/translate/version/{id}`

**Description:** Retrieves a lexical entry based on a specific version ID.

**Parameters:**
- `id` (integer, required) - The version ID of the lexical entry

**Response:**
- **200 OK** - Returns the lexical entry from the specified version

### 5. Search Entities

**Endpoint:** `POST /api/v3/book/entities/{groupId}/{entityId?}`

**Description:** Searches for entities within a specific group or retrieves a specific entity.

**Parameters:**
- `groupId` (integer, required) - The search group ID
- `entityId` (integer, optional) - Specific entity ID to retrieve

**Request Body (when entityId not provided):**
```json
{
    "query": "search term",
    "language_id": 1,
    "page": 1,
    "per_page": 20
}
```

**Response:**
- **200 OK** - Returns search results or specific entity

**Response Format:**
```json
{
    "entities": [
        {
            "id": 123,
            "title": "Entity Title",
            "content": "Entity content",
            "type": "lexical_entry",
            "relevance_score": 0.95
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 150,
        "last_page": 8
    }
}
```

### 6. Search Keywords

**Endpoint:** `POST /api/v3/book/find`

**Description:** Performs a keyword search across the dictionary with caching for performance.

**Request Body:**
```json
{
    "query": "search term",
    "language_id": 1,
    "group_id": 1,
    "page": 1,
    "per_page": 20
}
```

**Response:**
- **200 OK** - Returns keyword search results

**Response Format:**
```json
{
    "keywords": [
        {
            "id": 123,
            "keyword": "example",
            "type": "word",
            "language_id": 1,
            "group_id": 1,
            "relevance": 0.95
        }
    ],
    "search_groups": {
        "1": "Dictionary",
        "2": "Forum Posts",
        "3": "Sentences"
    }
}
```

## User Endpoints (Authentication Required)

### 7. Get Word by ID

**Endpoint:** `GET /api/v3/book/word/{id}`

**Description:** Retrieves a specific word by its ID.

**Authentication:** Requires user authentication

**Parameters:**
- `id` (integer, required) - The ID of the word to retrieve

**Response:**
- **200 OK** - Returns the word data
- **404 Not Found** - If no word exists with the given ID

**Response Format:**
```json
{
    "id": 123,
    "word": "example",
    "normalized_word": "example",
    "created_at": "2024-01-15T10:30:45.000Z",
    "updated_at": "2024-01-15T10:30:45.000Z"
}
```

### 8. Find Words

**Endpoint:** `POST /api/v3/book/word/find`

**Description:** Performs a forward search among words for the specified word parameter.

**Authentication:** Requires user authentication

**Request Body:**
```json
{
    "word": "example",
    "max": 10
}
```

**Parameters:**
- `word` (string, required, max:64) - The word to search for
- `max` (integer, optional) - Maximum number of results to return

**Response:**
- **200 OK** - Returns matching words

**Response Format:**
```json
[
    {
        "id": 123,
        "word": "example"
    },
    {
        "id": 124,
        "word": "examples"
    }
]
```

## Data Models

### LexicalEntryGroup
Dictionary category information:
- **id:** Unique identifier
- **name:** Group name
- **label:** Short label
- **is_canon:** Whether it's canonical content
- **is_old:** Whether it's historical content
- **external_link_format:** Format for external links

### Language
Language information:
- **id:** Unique identifier
- **name:** Language name
- **order:** Display order
- **category:** Language category (constructed/natural)

### Word
Basic word information:
- **id:** Unique identifier
- **word:** The actual word
- **normalized_word:** Normalized form for searching

## Performance Features

### Caching Strategy
- **Languages:** Cached for 1 month
- **Keywords:** Cached for 1 day with hash-based keys
- **Entities:** Cached for 1 day with group-specific keys
- **Search Groups:** Cached for 1 hour with locale-specific keys

### Search Optimization
- **Normalized Search:** All searches use normalized word forms
- **Hash-based Caching:** Cache keys include search parameter hashes
- **Pagination:** Built-in pagination for large result sets
- **Group Filtering:** Efficient group-based filtering

## Error Handling

### Common Error Responses

**404 Not Found:**
```json
null
```

**422 Validation Error:**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "word": ["The word field is required."]
    }
}
```

**401 Unauthorized:**
```json
{
    "message": "Unauthenticated."
}
```

## Usage Examples

### Frontend Integration
```javascript
// Get all languages
const getLanguages = async () => {
    const response = await fetch('/api/v3/book/languages');
    return response.json();
};

// Search for lexical entries
const searchEntries = async (query, languageId = 0) => {
    const response = await fetch('/api/v3/book/find', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            query,
            language_id: languageId
        })
    });
    return response.json();
};

// Get specific lexical entry
const getLexicalEntry = async (id) => {
    const response = await fetch(`/api/v3/book/translate/${id}`);
    if (response.status === 404) {
        return null;
    }
    return response.json();
};

// Find words with authentication
const findWords = async (word, max = 10) => {
    const response = await fetch('/api/v3/book/word/find', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            word,
            max
        })
    });
    return response.json();
};
```

### Search Implementation
```javascript
// Advanced search with multiple criteria
const advancedSearch = async (searchParams) => {
    const response = await fetch('/api/v3/book/entities/1', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            query: searchParams.query,
            language_id: searchParams.languageId || 0,
            page: searchParams.page || 1,
            per_page: searchParams.perPage || 20
        })
    });
    
    const data = await response.json();
    return {
        results: data.entities,
        pagination: data.pagination,
        searchGroups: data.search_groups
    };
};
```

## Implementation Details

### Repository Integration
The controller uses several repositories:
- **SearchIndexRepository** - For keyword and entity searches
- **LexicalEntryRepository** - For lexical entry operations
- **DiscussRepository** - For forum integration
- **LexicalEntryInflectionRepository** - For inflection handling

### Adapter Pattern
- **BookAdapter** - Transforms data for API responses
- **Consistent formatting** across all endpoints
- **View model preparation** for frontend consumption

### Search Algorithm
1. **Query Normalization** - Input queries are normalized
2. **Hash Generation** - Search parameters are hashed for caching
3. **Multi-table Joins** - Efficient database queries across related tables
4. **Result Ranking** - Results are ordered by relevance
5. **Pagination** - Large result sets are paginated

## Related Documentation
- [LexicalEntryApiController](./LEXICAL_ENTRY_API_CONTROLLER.md)
- [DiscussApiController](./DISCUSS_API_CONTROLLER.md)
- [Queue Job Statistics](./QUEUE_JOB_STATISTICS.md)
- [Expensive Requests Middleware](./EXPENSIVE_REQUESTS_MIDDLEWARE.md)
