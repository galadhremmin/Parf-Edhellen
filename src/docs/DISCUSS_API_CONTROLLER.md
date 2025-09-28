# DiscussApiController Documentation

The `DiscussApiController` provides API endpoints for managing forum discussions, threads, and posts in the Parf-Edhellen system. This controller handles the complete forum functionality including creating, reading, updating, and deleting posts and threads.

## Overview

The controller provides endpoints for:
- **Forum Management** - Get groups, threads, and posts
- **Discussion Creation** - Create new posts and threads
- **Content Moderation** - Update, delete, and manage forum content
- **Thread Navigation** - Resolve threads from entities and posts
- **Social Features** - Like posts and manage thread properties

## API Endpoints

### Base URL
All endpoints are prefixed with `/api/v3/discuss/` where `v3` is the current API version.

## Public Endpoints (No Authentication Required)

### 1. Get Forum Groups

**Endpoint:** `GET /api/v3/discuss/group`

**Description:** Retrieves all available forum groups (discussion categories).

**Response:**
- **200 OK** - Returns array of forum groups

**Response Format:**
```json
[
    {
        "id": 1,
        "name": "General Discussion",
        "description": "General topics and discussions",
        "order": 1,
        "is_active": true,
        "created_at": "2024-01-15T10:30:45.000Z",
        "updated_at": "2024-01-15T10:30:45.000Z"
    }
]
```

### 2. Get Group and Threads

**Endpoint:** `GET /api/v3/discuss/group/{groupId}`

**Description:** Retrieves a specific forum group and its associated threads with pagination.

**Parameters:**
- `groupId` (integer, required) - The ID of the forum group

**Query Parameters:**
- `offset` (integer, optional) - Page offset for pagination (default: 0)

**Response:**
- **200 OK** - Returns group and threads data

**Response Format:**
```json
{
    "group": {
        "id": 1,
        "name": "General Discussion",
        "description": "General topics and discussions"
    },
    "threads": [
        {
            "id": 123,
            "subject": "Discussion about Sindarin",
            "normalized_subject": "discussion-about-sindarin",
            "forum_group_id": 1,
            "sticky": false,
            "locked": false,
            "post_count": 5,
            "last_post_at": "2024-01-15T14:30:45.000Z",
            "created_at": "2024-01-15T10:30:45.000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 5,
        "total_threads": 25
    }
}
```

### 3. Get Latest Threads

**Endpoint:** `GET /api/v3/discuss/thread`

**Description:** Retrieves the latest forum threads across all groups based on their most recent posts.

**Response:**
- **200 OK** - Returns latest threads

**Response Format:**
```json
[
    {
        "id": 123,
        "subject": "Latest Discussion",
        "forum_group_id": 1,
        "forum_group": {
            "name": "General Discussion"
        },
        "last_post_at": "2024-01-15T14:30:45.000Z",
        "post_count": 3
    }
]
```

### 4. Get Thread

**Endpoint:** `GET /api/v3/discuss/thread/{threadId}`

**Description:** Retrieves a specific thread with its posts and metadata.

**Parameters:**
- `threadId` (integer, required) - The ID of the thread

**Query Parameters:**
- `offset` (integer, optional) - Page offset for pagination
- `forum_post_id` (integer, optional) - Jump to specific post ID

**Response:**
- **200 OK** - Returns thread and posts data

**Response Format:**
```json
{
    "thread": {
        "id": 123,
        "subject": "Discussion about Sindarin",
        "normalized_subject": "discussion-about-sindarin",
        "forum_group_id": 1,
        "forum_group": {
            "name": "General Discussion"
        },
        "sticky": false,
        "locked": false,
        "created_at": "2024-01-15T10:30:45.000Z"
    },
    "posts": [
        {
            "id": 456,
            "content": "This is the first post in the thread.",
            "forum_thread_id": 123,
            "account_id": 789,
            "account": {
                "nickname": "user123"
            },
            "created_at": "2024-01-15T10:30:45.000Z",
            "updated_at": "2024-01-15T10:30:45.000Z",
            "is_deleted": false
        }
    ],
    "pagination": {
        "current_page": 1,
        "total_pages": 3,
        "total_posts": 15
    }
}
```

### 5. Get Thread by Entity

**Endpoint:** `GET /api/v3/discuss/thread/{entityType}/{entityId}`

**Description:** Retrieves or creates a thread associated with a specific entity (lexical entry, etc.).

**Parameters:**
- `entityType` (string, required) - Type of entity (e.g., "lexical_entry")
- `entityId` (integer, required) - ID of the entity

**Response:**
- **200 OK** - Returns thread data
- **404 Not Found** - If entity doesn't exist

### 6. Get Post

**Endpoint:** `GET /api/v3/discuss/post/{postId}`

**Description:** Retrieves a specific forum post with optional markdown processing.

**Parameters:**
- `postId` (integer, required) - The ID of the post

**Query Parameters:**
- `include_deleted` (boolean, optional) - Include deleted posts (requires permissions)
- `markdown` (boolean, optional) - Return raw markdown content (default: false)

**Response:**
- **200 OK** - Returns post data
- **404 Not Found** - If post doesn't exist

**Response Format:**
```json
{
    "post": {
        "id": 456,
        "content": "This is the post content.",
        "forum_thread_id": 123,
        "account_id": 789,
        "account": {
            "nickname": "user123"
        },
        "created_at": "2024-01-15T10:30:45.000Z",
        "updated_at": "2024-01-15T10:30:45.000Z",
        "is_deleted": false,
        "like_count": 5
    }
}
```

### 7. Resolve Thread from Entity

**Endpoint:** `GET /api/v3/discuss/thread/resolve/{entityType}/{entityId}`

**Description:** Resolves or creates a thread for a specific entity. Returns JSON for AJAX requests or redirects for regular requests.

**Parameters:**
- `entityType` (string, required) - Type of entity
- `entityId` (integer, required) - ID of the entity

**Query Parameters:**
- `create` (boolean, optional) - Create thread if it doesn't exist (default: false)

**Response:**
- **200 OK** - Returns thread data (AJAX) or redirects (regular request)
- **404 Not Found** - If entity doesn't exist and create is false

### 8. Resolve Thread from Post

**Endpoint:** `GET /api/v3/discuss/thread/resolve-by-post/{postId}`

**Description:** Resolves the thread containing a specific post. Returns JSON for AJAX requests or redirects for regular requests.

**Parameters:**
- `postId` (integer, required) - The ID of the post

**Response:**
- **200 OK** - Returns thread data (AJAX) or redirects (regular request)
- **404 Not Found** - If post doesn't exist

## Authenticated Endpoints (Authentication Required)

### 9. Get Thread Metadata

**Endpoint:** `POST /api/v3/discuss/thread/metadata`

**Description:** Retrieves metadata for multiple posts within a thread.

**Authentication:** Requires user authentication

**Request Body:**
```json
{
    "forum_thread_id": 123,
    "forum_post_id": [456, 789, 101112]
}
```

**Parameters:**
- `forum_thread_id` (integer, required) - The thread ID
- `forum_post_id` (array, required) - Array of post IDs

**Response:**
- **200 OK** - Returns metadata for posts

### 10. Create Post

**Endpoint:** `POST /api/v3/discuss/post`

**Description:** Creates a new forum post. Can create a new thread or reply to existing thread.

**Authentication:** Requires user authentication

**Request Body (Reply to existing thread):**
```json
{
    "content": "This is my reply to the thread.",
    "forum_thread_id": 123
}
```

**Request Body (Create new thread):**
```json
{
    "content": "This is the first post in a new thread.",
    "entity_type": "lexical_entry",
    "entity_id": 456,
    "forum_group_id": 1,
    "subject": "New Discussion Topic"
}
```

**Parameters:**
- `content` (string, required, min:1) - Post content
- `forum_thread_id` (integer, optional) - Thread ID for replies
- `entity_type` (string, required for new threads) - Type of entity
- `entity_id` (integer, optional) - Entity ID for new threads
- `forum_group_id` (integer, optional) - Forum group ID
- `subject` (string, optional, min:3, max:512) - Thread subject

**Response:**
- **201 Created** - Returns created post data
- **403 Forbidden** - If user lacks permissions

**Response Format:**
```json
{
    "post": {
        "id": 456,
        "content": "This is the post content.",
        "forum_thread_id": 123,
        "account_id": 789,
        "created_at": "2024-01-15T10:30:45.000Z"
    },
    "postUrl": "https://your-domain.com/discuss/general/123/discussion-topic#post-456",
    "thread": {
        "id": 123,
        "subject": "Discussion Topic",
        "forum_group_id": 1
    }
}
```

### 11. Update Post

**Endpoint:** `PUT /api/v3/discuss/post/{postId}`

**Description:** Updates an existing forum post and optionally the thread subject.

**Authentication:** Requires user authentication and post ownership

**Parameters:**
- `postId` (integer, required) - The ID of the post to update

**Request Body:**
```json
{
    "content": "Updated post content",
    "subject": "Updated Thread Subject"
}
```

**Parameters:**
- `content` (string, required, min:1) - Updated post content
- `subject` (string, optional, min:3, max:512) - Updated thread subject

**Response:**
- **200 OK** - Post updated successfully
- **403 Forbidden** - If user lacks permissions
- **404 Not Found** - If post doesn't exist

### 12. Delete Post

**Endpoint:** `DELETE /api/v3/discuss/post/{postId}`

**Description:** Deletes a forum post.

**Authentication:** Requires user authentication and post ownership

**Parameters:**
- `postId` (integer, required) - The ID of the post to delete

**Response:**
- **200 OK** - Post deleted successfully
- **400 Bad Request** - If deletion failed
- **404 Not Found** - If post doesn't exist

### 13. Like Post

**Endpoint:** `POST /api/v3/discuss/like`

**Description:** Likes or unlikes a forum post.

**Authentication:** Requires user authentication

**Request Body:**
```json
{
    "forum_post_id": 456
}
```

**Parameters:**
- `forum_post_id` (integer, required) - The ID of the post to like

**Response:**
- **200 OK** - Like status updated
- **400 Bad Request** - If operation failed

**Response Format:**
```json
{
    "like": {
        "id": 789,
        "forum_post_id": 456,
        "account_id": 123,
        "created_at": "2024-01-15T10:30:45.000Z"
    }
}
```

### 14. Update Thread Stickiness

**Endpoint:** `PUT /api/v3/discuss/thread/stick`

**Description:** Updates the sticky status of a thread (admin only).

**Authentication:** Requires admin privileges

**Request Body:**
```json
{
    "forum_thread_id": 123,
    "sticky": true
}
```

**Parameters:**
- `forum_thread_id` (integer, required) - The thread ID
- `sticky` (boolean, required) - Sticky status

**Response:**
- **200 OK** - Thread stickiness updated

### 15. Move Thread

**Endpoint:** `PUT /api/v3/discuss/thread/move`

**Description:** Moves a thread to a different forum group (admin only).

**Authentication:** Requires admin privileges

**Request Body:**
```json
{
    "forum_thread_id": 123,
    "forum_group_id": 2
}
```

**Parameters:**
- `forum_thread_id` (integer, required) - The thread ID
- `forum_group_id` (integer, required) - Target forum group ID

**Response:**
- **200 OK** - Thread moved successfully

## Data Models

### ForumGroup
Forum discussion category:
- **id:** Unique identifier
- **name:** Group name
- **description:** Group description
- **order:** Display order
- **is_active:** Whether group is active

### ForumThread
Discussion thread:
- **id:** Unique identifier
- **subject:** Thread subject
- **normalized_subject:** URL-friendly subject
- **forum_group_id:** Associated group ID
- **sticky:** Whether thread is sticky
- **locked:** Whether thread is locked
- **post_count:** Number of posts
- **last_post_at:** Timestamp of last post

### ForumPost
Individual post in a thread:
- **id:** Unique identifier
- **content:** Post content (markdown)
- **forum_thread_id:** Parent thread ID
- **account_id:** Author account ID
- **is_deleted:** Deletion status
- **like_count:** Number of likes

## Error Handling

### Common Error Responses

**404 Not Found:**
```json
null
```

**403 Forbidden:**
```json
null
```

**400 Bad Request:**
```json
null
```

**422 Validation Error:**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "content": ["The content field is required."]
    }
}
```

## Usage Examples

### Frontend Integration
```javascript
// Get forum groups
const getForumGroups = async () => {
    const response = await fetch('/api/v3/discuss/group');
    return response.json();
};

// Get threads in a group
const getGroupThreads = async (groupId, page = 0) => {
    const response = await fetch(`/api/v3/discuss/group/${groupId}?offset=${page}`);
    return response.json();
};

// Get specific thread
const getThread = async (threadId, page = 0) => {
    const response = await fetch(`/api/v3/discuss/thread/${threadId}?offset=${page}`);
    return response.json();
};

// Create a new post
const createPost = async (content, threadId = null, entityType = null, entityId = null) => {
    const response = await fetch('/api/v3/discuss/post', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            content,
            ...(threadId ? { forum_thread_id: threadId } : {}),
            ...(entityType ? { entity_type: entityType, entity_id: entityId } : {})
        })
    });
    return response.json();
};

// Like a post
const likePost = async (postId) => {
    const response = await fetch('/api/v3/discuss/like', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            forum_post_id: postId
        })
    });
    return response.json();
};
```

### Thread Resolution
```javascript
// Resolve thread from entity (for AJAX requests)
const resolveThreadFromEntity = async (entityType, entityId, create = false) => {
    const response = await fetch(`/api/v3/discuss/thread/resolve/${entityType}/${entityId}?create=${create}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    });
    return response.json();
};

// Get post with markdown
const getPostWithMarkdown = async (postId) => {
    const response = await fetch(`/api/v3/discuss/post/${postId}?markdown=true`);
    return response.json();
};
```

## Implementation Details

### Repository Integration
The controller uses:
- **DiscussRepository** - For forum operations
- **DiscussAdapter** - For data transformation

### Pagination
- **Offset-based pagination** using `offset` parameter
- **Configurable page sizes** through repository settings
- **Consistent pagination metadata** across endpoints

### Authentication & Authorization
- **User authentication** required for write operations
- **Post ownership** validation for updates/deletes
- **Admin privileges** required for moderation actions
- **Role-based access control** for sensitive operations

### Content Processing
- **Markdown support** for post content
- **Content sanitization** for security
- **URL generation** for post links
- **Entity association** for contextual discussions

## Related Documentation
- [BookApiController](./BOOK_API_CONTROLLER.md)
- [LexicalEntryApiController](./LEXICAL_ENTRY_API_CONTROLLER.md)
- [Queue Job Statistics](./QUEUE_JOB_STATISTICS.md)
- [Expensive Requests Middleware](./EXPENSIVE_REQUESTS_MIDDLEWARE.md)
