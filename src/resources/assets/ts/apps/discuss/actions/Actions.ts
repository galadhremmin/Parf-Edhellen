enum Actions {
    RequestThread = 'ED_DISCUSS_THREAD_REQUEST',
    ReceiveThread = 'ED_DISCUSS_THREAD',

    RequestPost = 'ED_DISCUSS_POST_REQUEST',
    ReceivePost = 'ED_DISCUSS_POST',

    RequestThreadMetadata = 'ED_DISCUSS_THREAD_METADATA_REQUEST',
    ReceiveThreadMetadata = 'ED_DISCUSS_THREAD_METADATA',

    RequestCreatePost = 'ED_DISCUSS_CREATE_POST_REQUEST',
    ReceiveCreatePost = 'ED_DISCUSS_CREATE_POST',

    ChangeNewPost  = 'ED_DISCUSS_NEW_POST_CHANGE',
    CreateNewPost  = 'ED_DISCUSS_NEW_POST',
    DiscardNewPost = 'ED_DISCUSS_NEW_POST_DISCARD',
}

export default Actions;
