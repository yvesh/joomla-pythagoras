<?php
namespace Joomla\Cms\Http;

class AcceptLanguageHeader extends QualifiedHeader
{
	public function __construct($header)
	{
		parent::__construct($header, '-', '');
	}
}
