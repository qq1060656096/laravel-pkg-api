<?php
namespace Zwei\LaravelPkgApi\Domain\Entity;


class PageEntity extends Entity
{
    /**
     * @var integer
     */
    protected $page = 1;
    
    /**
     * @var integer
     */
    protected $pageSize = 10;
    /**
     * @var integer
     */
    protected $count;
    
    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }
    
    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $page = intval($page);
        $this->page = max($page, 1);
    }
    
    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }
    
    /**
     * @param int $pageSize
     */
    public function setPageSize($pageSize)
    {
        $pageSize = intval($pageSize);
        $this->pageSize = min($pageSize, 100);
    }
    
    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
    
    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }
    
    /**
     * @return integer
     */
    public function getOffset()
    {
        return ($this->getPage() - 1) * $this->getPageSize();
    }
    
    /**
     * @return integer
     */
    public function getPageCount()
    {
        if (intval($this->getCount()) < 1 || $this->getPageSize() < 1) {
            return 0;
        }
        $pageCount = ceil($this->getCount()/$this->getPageSize());
        return $pageCount;
    }
    
    
}
