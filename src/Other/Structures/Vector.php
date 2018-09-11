<?php

namespace Rubix\ML\Other\Structures;

use InvalidArgumentException;
use IteratorAggregate;
use RuntimeException;
use ArrayIterator;
use ArrayAccess;
use Countable;

/**
 * Vector
 *
 * One dimensional tensor with integer and/or floating point elements.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class Vector implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * The 1-d array that holds the values of the vector.
     *
     * @var array
     */
    protected $a = [
        //
    ];

    /**
     * The elements in the vector.
     *
     * @var int
     */
    protected $n;

    /**
     * Factory method to build a new vector from an array.
     *
     * @param  array  $a
     * @param  bool  $validate
     * @return self
     */
    public static function build(array $a, bool $validate = true) : self
    {
        return new self($a, $validate);
    }

    /**
     * Build a vector of zeros with n elements.
     *
     * @param  int  $n
     * @return self
     */
    public static function zeros(int $n) : self
    {
        return new self(array_fill(0, $n, 0), false);
    }

    /**
     * Build a vector of ones with n elements.
     *
     * @param  int  $n
     * @return self
     */
    public static function ones(int $n) : self
    {
        return new self(array_fill(0, $n, 1), false);
    }

    /**
     * @param  (int|float)[]  $a
     * @param  bool  $validate
     * @throws \InvalidArgumentException
     * @return void
     */
    public function __construct(array $a, bool $validate = true)
    {
        if ($validate === true) {
            $a = array_values($a);

            foreach ($a as $value) {
                if (!is_int($value) and !is_float($value)) {
                    throw new InvalidArgumentException('Vector element must'
                        . ' be an integer or float, '
                        . gettype($value) . ' found.');
                }
            }
        }

        $this->a = $a;
        $this->n = count($a);
    }

    /**
     * Return the number of elements in the vector i.e the dimensionality.
     *
     * @return int
     */
    public function n() : int
    {
        return $this->n;
    }

    /**
     * Return the vector as an array.
     *
     * @return array
     */
    public function asArray() : array
    {
        return $this->a;
    }

    /**
     * Return this vector as a row matrix.
     *
     * @return \Rubix\ML\Other\Structures\Matrix
     */
    public function asRowMatrix() : Matrix
    {
        return new Matrix([$this->a], false);
    }

    /**
     * Return this vector as a column matrix.
     *
     * @return \Rubix\ML\Other\Structures\Matrix
     */
    public function asColumnMatrix() : Matrix
    {
        return new Matrix(array_map(function ($value) {
            return [$value];
        }, $this->a), false);
    }

    /**
     * Map a function over the elements in the vector and return a new vector.
     *
     * @param  callable  $fn
     * @return self
     */
    public function map(callable $fn) : self
    {
        return new self(array_map($fn, $this->a));
    }

    /**
     * Reduce the vector down to a scalar.
     *
     * @param  callable  $fn
     * @param  float  $initial
     * @return float
     */
    public function reduce(callable $fn, float $initial = 0.) : float
    {
        return array_reduce($this->a, $fn, $initial);
    }

    /**
     * Compute the dot product of this vector and another vector.
     *
     * @param  \Rubix\ML\Other\Structures\Vector  $b
     * @throws \InvalidArgumentException
     * @return float
     */
    public function dot(Vector $b) : float
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vectors do not have the same'
                . ' dimensionality.');
        }

        $product = 0.;

        foreach ($this->a as $i => $value) {
            $product += $value * $b[$i];
        }

        return $product;
    }

    /**
     * Return the inner product of two vectors. Alias of dot().
     *
     * @param  \Rubix\ML\Other\Structures\Vector  $b
     * @return float
     */
    public function inner(Vector $b) : float
    {
        return $this->dot($b);
    }

    /**
     * Calculate the outer product of this and another vector. Return as Matrix.
     *
     * @param  \Rubix\ML\Other\Structures\Vector  $b
     * @return \Rubix\ML\Other\Structures\Matrix
     */
    public function outer(Vector $b) : Matrix
    {
        $n = $b->n();

        $c = [[]];

        foreach ($this->a as $i => $value) {
            for ($j = 0; $j < $n; $j++) {
                $c[$i][$j] = $value * $b[$j];
            }
        }

        return new Matrix($c);
    }

    /**
     * The sum of the vector.
     *
     * @return float
     */
    public function sum() : float
    {
        return (float) array_sum($this->a);
    }

    /**
     * Return the product of the vector.
     *
     * @return float
     */
    public function product() : float
    {
        return (float) array_product($this->a);
    }

    /**
     * Multiply this vector with another vector.
     *
     * @param  \Rubix\ML\Other\Structures\Vector  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function multiply(Vector $b) : self
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vectors do not have the same'
                . ' dimensionality.');
        }

        $c = [];

        foreach ($this->a as $i => $value) {
            $c[$i] = $value * $b[$i];
        }

        return new self($c, false);
    }

    /**
     * Divide this vector by another vector.
     *
     * @param  \Rubix\ML\Other\Structures\Vector  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function divide(Vector $b) : self
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vectors do not have the same'
                . ' dimensionality.');
        }

        $c = [];

        foreach ($this->a as $i => $value) {
            $c[$i] = $value / $b[$i];
        }

        return new self($c, false);
    }

    /**
     * Add this vector to another vector.
     *
     * @param  \Rubix\ML\Other\Structures\Vector  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function add(Vector $b) : self
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vectors do not have the same'
                . ' dimensionality.');
        }

        $c = [];

        foreach ($this->a as $i => $value) {
            $c[$i] = $value + $b[$i];
        }

        return new self($c, false);
    }

    /**
     * Subtract this vector from another vector.
     *
     * @param  \Rubix\ML\Other\Structures\Vector  $b
     * @throws \InvalidArgumentException
     * @return self
     */
    public function subtract(Vector $b) : self
    {
        if ($this->n !== $b->n()) {
            throw new InvalidArgumentException('Vectors do not have the same'
                . ' dimensionality.');
        }

        $c = [];

        foreach ($this->a as $i => $value) {
            $c[$i] = $value - $b[$i];
        }

        return new self($c, false);
    }

    /**
     * Multiply this matrix by a scalar.
     *
     * @param  mixed  $scalar
     * @throws \InvalidArgumentException
     * @return self
     */
    public function multiplyScalar($scalar) : self
    {
        if (!is_int($scalar) and !is_float($scalar)) {
            throw new InvalidArgumentException('Scalar must be an integer or'
                . ' float ' . gettype($scalar) . ' found.');
        }

        $b = [];

        foreach ($this->a as $i => $value) {
            $b[$i] = $value * $scalar;
        }

        return new self($b, false);
    }

    /**
     * Divide this matrix by a scalar.
     *
     * @param  mixed  $scalar
     * @throws \InvalidArgumentException
     * @return self
     */
    public function divideScalar($scalar) : self
    {
        if (!is_int($scalar) and !is_float($scalar)) {
            throw new InvalidArgumentException('Scalar must be an integer or'
                . ' float ' . gettype($scalar) . ' found.');
        }

        $b = [];

        foreach ($this->a as $i => $value) {
            $b[$i] = $value / $scalar;
        }

        return new self($b, false);
    }

    /**
     * Add this matrix by a scalar.
     *
     * @param  mixed  $scalar
     * @throws \InvalidArgumentException
     * @return self
     */
    public function addScalar($scalar) : self
    {
        if (!is_int($scalar) and !is_float($scalar)) {
            throw new InvalidArgumentException('Factor must be an integer or'
                . ' float ' . gettype($scalar) . ' found.');
        }

        $b = [];

        foreach ($this->a as $i => $value) {
            $b[$i] = $value + $scalar;
        }

        return new self($b, false);
    }

    /**
     * Subtract this matrix by a scalar.
     *
     * @param  mixed  $scalar
     * @throws \InvalidArgumentException
     * @return self
     */
    public function subtractScalar($scalar) : self
    {
        if (!is_int($scalar) and !is_float($scalar)) {
            throw new InvalidArgumentException('Scalar must be an integer or'
                . ' float ' . gettype($scalar) . ' found.');
        }

        $b = [];

        foreach ($this->a as $i => $value) {
            $b[$i] = $value - $scalar;
        }

        return new self($b, false);
    }

    /**
     * Take the absolute value of the vector.
     *
     * @return self
     */
    public function abs() : self
    {
        $b = [];

        foreach ($this->a as $value) {
            $b[] = abs($value);
        }

        return new self($b, false);
    }

    /**
     * Square the vector.
     *
     * @return self
     */
    public function square() : self
    {
        $b = [];

        foreach ($this->a as $value) {
            $b[] = $value ** 2;
        }

        return new self($b, false);
    }

    /**
     * Take the square root of the vector.
     *
     * @return self
     */
    public function sqrt() : self
    {
        $b = [];

        foreach ($this->a as $value) {
            $b[] = $value ** 0.5;
        }

        return new self($b, false);
    }

    /**
     * Exponentiate each element in the vector.
     *
     * @return self
     */
    public function exp() : self
    {
        $b = [];

        foreach ($this->a as $value) {
            $b[] = M_E ** $value;
        }

        return new self($b, false);
    }

    /**
     * Exponentiate each element in the vector.
     *
     * @param  float  $base
     * @return self
     */
    public function log(float $base = M_E) : self
    {
        $b = [];

        foreach ($this->a as $value) {
            $b[] = log($value, $base);
        }

        return new self($b, false);
    }

    /**
     * Return the minimum element in the vector.
     *
     * @return float
     */
    public function min() : float
    {
        return (float) min($this->a);
    }

    /**
     * Return the maximum element in the vector.
     *
     * @return float
     */
    public function max() : float
    {
        return (float) max($this->a);
    }

    /**
     * Return the mean of the vector.
     *
     * @return float
     */
    public function mean() : float
    {
        return array_sum($this->a) / $this->n;
    }

    /**
     * Calculate the L1 or Manhattan norm of the vector.
     *
     * @return float
     */
    public function l1Norm() : float
    {
        return (float) array_sum(array_map('abs', $this->a));
    }

    /**
     * Calculate the L2 or Euclidean norm of the vector.
     *
     * @return float
     */
    public function l2Norm() : float
    {
        $norm = 0.;

        foreach ($this->a as $value) {
            $norm += $value ** 2;
        }

        return $norm ** 0.5;
    }

    /**
     * Calculate the max norm of the vector.
     *
     * @return float
     */
    public function maxNorm() : float
    {
        return (float) max(array_map('abs', $this->a));
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->n;
    }

    /**
     * @param  mixed  $index
     * @param  array  $values
     * @throws \RuntimeException
     * @return void
     */
    public function offsetSet($index, $values) : void
    {
        throw new RuntimeException('Vector cannot be mutated directly.');
    }

    /**
     * Does a given column exist in the matrix.
     *
     * @param  mixed  $index
     * @return bool
     */
    public function offsetExists($index) : bool
    {
        return isset($this->a[$index]);
    }

    /**
     * @param  mixed  $index
     * @throws \RuntimeException
     * @return void
     */
    public function offsetUnset($index) : void
    {
        throw new RuntimeException('Vector cannot be mutated directly.');
    }

    /**
     * Return a row from the matrix at the given index.
     *
     * @param  mixed  $index
     * @throws \InvalidArgumentException
     * @return int|float
     */
    public function offsetGet($index)
    {
        if (!isset($this->a[$index])) {
            throw new InvalidArgumentException('Element not found at index'
                . (string) $index . '.');
        }

        return $this->a[$index];
    }

    /**
     * Get an iterator for the rows in the matrix.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->a);
    }
}
