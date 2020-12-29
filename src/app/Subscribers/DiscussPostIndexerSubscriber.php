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
use App\Repositories\MailSettingRepository;
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
    public function __construct(IIdentifiesPhrases $analyzer)
    {
        $this->_analyzer = $analyzer;
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
        
        // TODO: Associate keywords with the ForumPost entity.
    }
}
