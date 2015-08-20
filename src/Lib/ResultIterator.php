<?php

namespace SymphonyPDO\Lib;

/**
 * This ResultIterator accepts a PDOStatment class containing data.
 * It also is provided a classname. As the statement set is iterated over
 * new instances of the classname provded are created and the data for the current
 * row is loaded in. The new class instance is then returned.
 */
class ResultIterator implements \Iterator, \Countable
{
    /**
     * @var PDOStatement
     *                   A statment containing data to iterator over
     */
    protected $statement;

    /**
     * @var int
     *          Tracks which row in the statement we are up to
     */
    protected $position;

    /**
     * @var object
     *             Holds an instance of $this->className containing the current row of data
     */
    protected $current;

    /**
     * @var int
     *          Used by the current() method. Allows current() to be called multiple times
     *          without advancing the cursor
     */
    protected $lastPosition;

    /**
     * The constructor accepts the `$className`, `$statement`.
     *
     * @param string        $className
     * @param \PDOStatement $statement
     */
    public function __construct($className, \PDOStatement $statement)
    {
        $this->statement = $statement;
        $this->className = $className;
        $this->current = null;
        $this->position = 0;
        $this->lastPosition = -1;

        // Sanity Check: Make sure that $className actually exists since PDOStatement::setFetchMode() does
        // not complain if the class specified is non-existent. Then any fetch methods on the result will
        // silently return an associative array instead of the intended class.
        if (!class_exists($className)) {
            throw new \Exception(sprintf("Class '%s' does not exist.", $className));
        }

        // Anytime fetch() is called on the statement it will return an instance of
        // $className. The values of fields in the row are automatically injected into
        // the properties of the class. \PDO::FETCH_PROPS_LATE tells it to call the constructor
        // first.
        $this->statement->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $className);
    }

    /**
     * Returns the row count of statement.
     *
     * @return int
     */
    public function count()
    {
        return $this->statement->rowCount();
    }

    /**
     * Create a new instance of $this->className and by calling
     * the fetch() method on $this->statement;.
     *
     * @return object
     */
    public function current()
    {
        // Check if the lastPosition is different to the current position.
        // If it is, then get a new object and update lastPosition.
        if ($this->lastPosition !== $this->position) {
            $this->current = $this->statement->fetch();
            $this->lastPosition = $this->position;
        }

        return $this->current;
    }

    /**
     * returns the current cursor position.
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Increments the current cursor position. Also makes sure that
     * position is not create that the number of rows in data.
     *
     * @return bool
     */
    public function next()
    {
        ++$this->position;
        if ($this->position + 1 >= $this->count()) {
            return false;
        }

        return true;
    }

    /**
     * Executes the statement again, resetting the data and
     * changing the position to 0.
     */
    public function rewind()
    {
        $this->position = 0;
        $this->statement->execute();
    }

    /**
     * Checks that position is less than the total number of rows
     * in the data set.
     *
     * @return bool
     */
    public function valid()
    {
        return (bool) ($this->position < $this->count());
    }

    /**
     * Passes each record into $callback.
     *
     * @return int
     *             Returns the iterator count
     */
    public function each(callable $callback, array $args = [])
    {
        array_unshift($args, $this);

        return iterator_apply($this, $callback, $args);
    }
}
