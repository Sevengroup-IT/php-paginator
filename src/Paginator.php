<?php

namespace Sevengroup;

class Paginator
{
    const NUM_PLACEHOLDER = '(:num)';

    protected int $totalItems;
    protected int $numPages;
    protected int $itemsPerPage;
    protected int $currentPage;
    protected string $urlPattern;
    protected int $maxPagesToShow = 10;
    protected string $firstText = 'First';
    protected string $previousText = 'Previous';
    protected string $nextText = 'Next';
    protected string $lastText = 'Last';


    /**
     * @param int $totalItems The total number of items.
     * @param int $itemsPerPage The number of items per page.
     * @param int $currentPage The current page number.
     * @param string $urlPattern A URL for each page, with (:num) as a placeholder for the page number. Ex. '/foo/page/(:num)'
     */
    public function __construct($totalItems, $itemsPerPage, $currentPage, $urlPattern = '')
    {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
        $this->urlPattern = $urlPattern;

        $this->updateNumPages();
    }

    /**
    * @return void
    */
    protected function updateNumPages(): void
    {
        $this->numPages = ($this->itemsPerPage == 0 ? 0 : (int)ceil($this->totalItems / $this->itemsPerPage));
    }

    /**
     * @param int $maxPagesToShow
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     */
    public function setMaxPagesToShow(int $maxPagesToShow): void
    {
        if ($maxPagesToShow < 3) {
            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 3.');
        }
        $this->maxPagesToShow = $maxPagesToShow;
    }

    /**
     * @return int
     */
    public function getMaxPagesToShow(): int
    {
        return $this->maxPagesToShow;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage(int $itemsPerPage): void
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->updateNumPages();
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $totalItems
     */
    public function setTotalItems(int $totalItems): void
    {
        $this->totalItems = $totalItems;
        $this->updateNumPages();
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @return int
     */
    public function getNumPages(): int
    {
        return $this->numPages;
    }

    /**
     * @param string $urlPattern
     */
    public function setUrlPattern(string $urlPattern): void
    {
        $this->urlPattern = $urlPattern;
    }

    /**
     * @return string
     */
    public function getUrlPattern(): string
    {
        return $this->urlPattern;
    }

    /**
     * @param int $pageNum
     * @return string
     */
    public function getPageUrl(int $pageNum): string
    {
        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->urlPattern);
    }

    public function getFirstPage(): int
    {
        if ($this->currentPage > 1) {
            return 1;
        }

        return null;
    }

    /**
    * @return null | string
    */
    public function getFirstUrl(): mixed
    {
        if (!$this->getFirstPage()) {
            return null;
        }

        return $this->getPageUrl($this->getFirstPage());
    }

    /**
    * @return null | string
    */
    public function getLastPage(): mixed
    {
        if ($this->currentPage < $this->numPages) {
            return $this->numPages;
        }

        return null;
    }

    /**
    * @return null | string
    */
    public function getLastUrl(): mixed
    {
        if (!$this->getLastPage()) {
            return null;
        }

        return $this->getPageUrl($this->getLastPage());
    }

    /**
    * @return null | int
    */
    public function getNextPage(): mixed
    {
        if ($this->currentPage < $this->numPages) {
            return $this->currentPage + 1;
        }

        return null;
    }

    /**
    * @return null | int
    */    
    public function getPrevPage(): mixed
    {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        }

        return null;
    }

    /**
    * @return null | int
    */
    public function getNextUrl(): mixed
    {
        if (!$this->getNextPage()) {
            return null;
        }

        return $this->getPageUrl($this->getNextPage());
    }

    /**
     * @return string|null
     */
    public function getPrevUrl(): mixed
    {
        if (!$this->getPrevPage()) {
            return null;
        }

        return $this->getPageUrl($this->getPrevPage());
    }

    /**
     * Get an array of paginated page data.
     *
     * Example:
     * array(
     *     array ('num' => 1,     'url' => '/example/page/1',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 3,     'url' => '/example/page/3',  'isCurrent' => false),
     *     array ('num' => 4,     'url' => '/example/page/4',  'isCurrent' => true ),
     *     array ('num' => 5,     'url' => '/example/page/5',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 10,    'url' => '/example/page/10', 'isCurrent' => false),
     * )
     *
     * @return array
     */
    public function getPages(): array
    {
        $pages = array();

        if ($this->numPages <= 1) {
            return array();
        }

        if ($this->numPages <= $this->maxPagesToShow) {
            for ($i = 1; $i <= $this->numPages; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
        } else {

            // Determine the sliding range, centered around the current page.
            $numAdjacents = (int)floor(($this->maxPagesToShow - 3) / 2);

            if ($this->currentPage + $numAdjacents > $this->numPages) {
                $slidingStart = $this->numPages - $this->maxPagesToShow + 2;
            } else {
                $slidingStart = $this->currentPage - $numAdjacents;
            }
            if ($slidingStart < 2) $slidingStart = 2;

            $slidingEnd = $slidingStart + $this->maxPagesToShow - 3;
            if ($slidingEnd >= $this->numPages) $slidingEnd = $this->numPages - 1;

            // Build the list of pages.
            $pages[] = $this->createPage(1, $this->currentPage == 1);
            if ($slidingStart > 2) {
                $pages[] = $this->createPageEllipsis();
            }
            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
            if ($slidingEnd < $this->numPages - 1) {
                $pages[] = $this->createPageEllipsis();
            }
            $pages[] = $this->createPage($this->numPages, $this->currentPage == $this->numPages);
        }

        return $pages;
    }


    /**
     * Create a page data structure.
     *
     * @param int $pageNum
     * @param bool $isCurrent
     * @return Array
     */
    protected function createPage(int $pageNum, bool $isCurrent = false): array
    {
        return array(
            'num' => $pageNum,
            'url' => $this->getPageUrl($pageNum),
            'isCurrent' => $isCurrent,
        );
    }

    /**
     * @return array
     */
    protected function createPageEllipsis(): array
    {
        return array(
            'num' => '...',
            'url' => null,
            'isCurrent' => false,
        );
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function toHtml(): string
    {
        if ($this->numPages <= 1) {
            return '';
        }

        $html = '<ul class="pagination">';
        if ($this->getPrevUrl()) {
            $html .= '<li><a href="' . htmlspecialchars($this->getFirstUrl()) . '">|&laquo; ' . $this->getFirstText() . '</a></li>';
            $html .= '<li><a href="' . htmlspecialchars($this->getPrevUrl()) . '">&laquo; ' . $this->getPreviousText() . '</a></li>';
        }

        foreach ($this->getPages() as $page) {
            if ($page['url']) {
                $html .= '<li' . ($page['isCurrent'] ? ' class="active"' : '') . '><a href="' . htmlspecialchars($page['url']) . '">' . htmlspecialchars($page['num']) . '</a></li>';
            } else {
                $html .= '<li class="disabled"><span>' . htmlspecialchars($page['num']) . '</span></li>';
            }
        }

        if ($this->getNextUrl()) {
            $html .= '<li><a href="' . htmlspecialchars($this->getNextUrl()) . '">' . $this->getNextText() . ' &raquo;</a></li>';
            $html .= '<li><a href="' . htmlspecialchars($this->getLastUrl()) . '">' . $this->getLastText() . ' &raquo;|</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
    * @return null | int
    */
    public function getCurrentPageFirstItem(): mixed
    {
        $first = ($this->currentPage - 1) * $this->itemsPerPage + 1;

        if ($first > $this->totalItems) {
            return null;
        }

        return $first;
    }

    /**
    * @return null | int
    */
    public function getCurrentPageLastItem(): mixed
    {
        $first = $this->getCurrentPageFirstItem();
        if ($first === null) {
            return null;
        }

        $last = $first + $this->itemsPerPage - 1;
        if ($last > $this->totalItems) {
            return $this->totalItems;
        }

        return $last;
    }

    /**
    * @return string
    */
    public function getFirstText(): string
    {
        return $this->firstText;
    }

    /**
    * @return Paginator
    */
    public function setFirstText($text): Paginator
    {
        $this->firstText = $text;
        
        return $this;
    }

    /**
    * @return string
    */
    public function getPreviousText(): string
    {
        return $this->previousText;
    }

    /**
    * @return Paginator
    */
    public function setPreviousText($text): Paginator
    {
        $this->previousText = $text;
        
        return $this;
    }

    /**
    * @return string
    */
    public function getNextText(): string
    {
        return $this->nextText;
    }

    /**
    * @return Paginator
    */
    public function setNextText($text): Paginator
    {
        $this->nextText = $text;
        
        return $this;
    }

    /**
    * @return string
    */
    public function getLastText()
    {
        return $this->lastText;
    }

    /**
    * @return Paginator
    */
    public function setLastText($text): Paginator
    {
        $this->lastText = $text;
        
        return $this;
    }

}
