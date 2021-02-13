<?php

namespace App\Subscribers;

use Aws\Credentials\{
    AssumeRoleCredentialProvider,
    CredentialProvider,
    InstanceProfileProvider
};
use Aws\Sts\StsClient;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

use App\Interfaces\IIdentifiesPhrases;
use App\Repositories\{
    SearchIndexRepository,
    WordRepository
};
use App\Models\Initialization\Morphs;
use App\Models\{
    Account,
    ForumPost,
    ForumThread
};
use App\Events\{
    ForumPostCreated
};

class DiscussPostIndexerSubscriber
{
    private $_analyzer;
    private $_searchIndexRepository;
    private $_wordRepository;

    public function __construct(IIdentifiesPhrases $analyzer, SearchIndexRepository $searchIndexRepository, WordRepository $wordRepository)
    {
        $this->_analyzer = $analyzer;
        $this->_searchIndexRepository = $searchIndexRepository;
        $this->_wordRepository = $wordRepository;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            ForumPostCreated::class,
            self::class.'@onForumPostCreated'
        );
    }

    public function onForumPostCreated(ForumPostCreated $event)
    {
        $post = $event->post;
        $keywords = $this->_analyzer->detectKeyPhrases($post->content);
        
        $post = $event->post;

        foreach ($keywords as $keyword) {
            $word = $this->_wordRepository->save($keyword, $post->account_id);
            $this->_searchIndexRepository->createIndex($post, $word);
        }
    }
}
