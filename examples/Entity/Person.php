<?php

class Person extends \SOPM\BaseEntity
{
	protected $firstName;
	protected function validateFirstName($firstName)
	{
		return (preg_match('/\s/', $firstName) === 0) && (lcfirst($firstName) === strtolower($firstName));
	}

	protected $lastName;
	protected function validateLastName($name)
	{
		$this->validateFirstName($name);
	}

	protected $tags;
	protected function validateTags($tags)
	{
		return is_array($tags);
	}

	protected $age;
	protected function validateAge($age)
	{
		return is_int($age);
	}
}
