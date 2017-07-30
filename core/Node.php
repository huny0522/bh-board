<?php
/**
 * @author     Bang Hun <huny0522@gmail.com>
 * 17.07.29
 */

namespace BH\BHCss;

class Node {
	public $selector = false;
	public $data = false;

	/** @var Node */
	public $parent = false;

	/** @var Node */
	public $next = false;

	public function setChild() {
		$this->data = new self();
		$this->data->parent = &$this;
	}

	public function setNext() {
		$this->next = new self();
		$this->next->parent = &$this->parent;
	}

}
