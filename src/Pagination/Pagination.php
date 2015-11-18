<?php


	namespace LiftKit\Pagination;


	use LiftKit\Pagination\Exception\PaginationException;


	class Pagination
	{
		protected $page;
		protected $perPage;
		protected $total;

		protected $baseUrl;
		protected $pageParameter;


		public function __construct ($page, $perPage, $total, $baseUrl = null, $pageParameter = 'page')
		{
			$this->page          = $page ? : 1;
			$this->perPage       = $perPage;
			$this->total         = $total;
			$this->pageParameter = $pageParameter;

			if (! $baseUrl) {
				$queryParameters = $_GET;

				if (isset($queryParameters[$pageParameter])) {
					unset($queryParameters[$pageParameter]);
				}

				$queryString = http_build_query($queryParameters);
				$this->baseUrl = strtok($_SERVER['REQUEST_URI'], '?') . '?' . $queryString;
			}
		}


		public function getPage ()
		{
			return $this->page;
		}


		public function getPerPage ()
		{
			return $this->perPage;
		}


		public function getStartIndex ()
		{
			return ($this->page - 1) * $this->perPage;
		}


		public function getStart ()
		{
			return $this->getStartIndex() + 1;
		}


		public function getEndIndex ()
		{
			$calculated = ($this->perPage * $this->page) - 1;
			$last = $this->getLastIndex();

			return $calculated > $last ? $last : $calculated;
		}


		public function getEnd ()
		{
			return $this->getEndIndex() + 1;
		}


		public function getLastIndex ()
		{
			return $this->getTotal() - 1;
		}


		public function getTotal ()
		{
			return $this->total;
		}


		public function getPages ()
		{
			return ceil($this->getTotal() / $this->getPerPage());
		}


		public function getNextPage ()
		{
			if ($this->hasNextPage()) {
				return $this->getPage() + 1;
			} else {
				return null;
			}
		}


		public function getPreviousPage ()
		{
			if ($this->hasPreviousPage()) {
				return $this->getPage() - 1;
			} else {
				return null;
			}
		}


		public function hasNextPage ()
		{
			return $this->getEndIndex() != $this->getLastIndex();
		}


		public function hasPreviousPage ()
		{
			return $this->getPage() > 1;
		}


		public function getNextUrl ()
		{
			if ($this->hasNextPage()) {
				return $this->appendParameterToUrl($this->pageParameter, $this->getNextPage(), $this->baseUrl);
			} else {
				return null;
			}
		}


		public function getPreviousUrl ()
		{
			if ($this->hasPreviousPage()) {
				return $this->appendParameterToUrl($this->pageParameter, $this->getPreviousPage(), $this->baseUrl);
			} else {
				return null;
			}
		}


		public function getPageUrl ($page)
		{
			if ($this->isValid($page)) {
				return $this->appendParameterToUrl($this->pageParameter, $page, $this->baseUrl);
			} else {
				return null;
			}
		}


		public function isValid ($page = null)
		{
			if (is_null($page)) {
				$page = $this->getPage();
			}

			$isValidPageNumber = is_numeric($page);
			$isPositivePageNumber = ($page >= 1);
			$isNotBeyondLast = ($page <= $this->getPages());

			return $isValidPageNumber && $isPositivePageNumber && $isNotBeyondLast;
		}


		public function isValidPerPage ($perPage = null)
		{
			if (is_null($perPage)) {
				$perPage = $this->getPerPage();
			}

			return is_numeric($perPage) &&  ($perPage >= 1);
		}


		public function getPaginationRange ($range, $page = null)
		{
			if (is_null($page)) {
				$page = $this->getPage();
			}

			$radius = floor(($range - .5) / 2);
			$start = ceil($page - $radius);
			$end = ceil($page + $radius);

			$end += ($range - ($end - $start) - 1);

			$startDifference = 1 - $start;

			if ($startDifference > 0) {
				$end += $startDifference;
				$start = 1;
			}

			if ($end > $this->getPages()) {
				$end = $this->getPages();
			}

			if ($end < 1) {
				$end = 1;
			}

			return range($start, $end);
		}


		protected function appendParameterToUrl ($name, $value, $url)
		{
			$lastCharacter = substr($url, -1);

			if (in_array($lastCharacter, array('?', '&', ';'))) {
				return $url . urlencode($name) . '=' . urlencode($value);

			} else if (strstr($url, '?')) {
				return $url . '&' . urlencode($name) . '=' . urlencode($value);

			} else {
				return $url . '?' . urlencode($name) . '=' . urlencode($value);
			}
		}


		protected function verify ()
		{
			if (! $this->isValid()) {
				throw new PaginationException('Invalid page number.');
			}

			if (! $this->isValidPerPage()) {
				throw new PaginationException('Invalid number of records per page.');
			}
		}
	}