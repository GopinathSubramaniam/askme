<?php
/*
	AI by Gideon Greenspan and contributors
	http://www.activiacademy.com/

	File: qa-theme/Candy/qa-theme.php
	Description: Override base theme class for Candy theme


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.activiacademy.com/license.php
*/

class qa_html_theme extends qa_html_theme_base
{
	// use new ranking layout
	protected $ranking_block_layout = true;
	protected $theme = 'candy';

	public function nav_user_search() // reverse the usual order
	{
		$this->search();
		$this->nav('user');
	}
}
