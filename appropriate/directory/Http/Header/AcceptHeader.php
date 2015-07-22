<?php
namespace Joomla\Cms\Http;

class AcceptHeader extends QualifiedHeader
{
	public function __construct($header)
	{
		parent::__construct($header, '/', '*');
	}
}
