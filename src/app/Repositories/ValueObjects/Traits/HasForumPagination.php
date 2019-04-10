<?php

namespace App\Repositories\ValueObjects\Traits;

trait HasForumPagination 
{
    public function setupForumPagination(array $properties)
    {
        $this->initializeAll($properties, [
            'current_page', 'no_of_pages', 'pages'
        ]);
    }

    /**
     * @return int
     */
    public function getCurrentPage() 
    {
        return $this->getValue('current_page');
    }

    /**
     * @return int
     */
    public function getNoOfPages()
    {
        return $this->getValue('no_of_pages');
    }

    /**
     * @return array
     */
    public function getPages() 
    {
        return $this->getValue('pages');
    }
}
