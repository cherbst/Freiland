<?php
/*
Copyright (c) 2011, Christoph Herbst.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class smwimport_test
{
  static function get_links(){
	$data = array( array(
		'type'  => 'Link',
		'name' => 'SMW Test Link',
		'category' => 'SMW Test Category', 
		'short_description' => 'This is a link automtically added by smwimport.',
		'website' => 'http://www.smwimport.org')
	);
	return $data;
  }

  static function get_events(){
	for ( $i=0;$i<100;$i++){
		$event = array(
		'type'  => 'Veranstaltung',
		'label' => 'SMW Event'.$i,
		'title' => 'SMW Event'.$i,
		'eventtype'  => 'concert',
		'date_begin' => '2011-'.(($i%12)+1).'-'.(($i%29)+1).' 10:00',
		'date_end' => ($i%10==0)?('2011-'.(($i%12)+1).'-'.((($i+1)%29)+1).' 10:00'):'',
		'short_description' => 'SMW imported event '.$i,
		'long_description' => '<strong>Newer imported event content</strong>',
		'genre' => 'rock',
		'homepage' => array( 'www.test1.de','www.test2.de','www.test3.de'),
		'location' => 'Werkstatt',
		'house' => 'big house',
		'room' => '203',
		'age' => '18'
		);

		$data[] = $event;
	}
	$data[] = array(
		'type'  => 'Veranstaltung',
		'label' => 'Kollektiv Turmstrasse',
		'title' => 'Kollektiv Turmstrasse',
		'eventtype'  => 'concert',
		'date_begin' => '2011-06-02 10:00',
		'short_description' => 'Rebellion der Träumer',
		'long_description' => 'Das neue Rebellion der Träumer Konzert!',
		'genre' => 'rock',
		'homepage' => array( 'www.test1.de','www.test2.de','www.test3.de'),
		'location' => 'Werkstatt',
		'house' => 'big house',
		'room' => '203',
		'age' => '18',
		'banner' => array('url' => 'http://87.238.194.42/Kollektiv-Turmstrasse.jpg',
				'title' => 'Turmstrasse')
		);

	return $data;
  }

  static function get_news(){
	$data = array( array(
		'type'  => 'News',
		'label' => 'SMW News',
		'title' => 'SMW News title',
		'short_description' => 'SMW imported news',
		'long_description' => '<strong>New imported news content</strong>',
		'subtitle' => 'A news subtitle',
		'homepage' => 'www.test1.de',
		'homepagelabel' => 'A test link',
		'image' => array(
			'url' => 'http://www.webmonkey.com/wp-content/uploads/2010/06/wordpress-300x300.jpg',
			'title' => 'News image title')
		)
	);
	return $data;
  }

  static function get_press(){
	$data = array( array(
		'type'  => 'Pressebericht',
		'label' => 'SMW Press',
		'title' => 'SMW Press',
		'date'  => '1.1.2011',
		'source' => 'Bild am Sonntag',
		'subtitle' => 'SMW imported press',
		'description' => '<strong>New imported press content</strong>',
		'homepage' => 'www.test1.de'
		)
	);
	return $data;
  }

  static function get_images(){
	$data = array( array(
		'type'  => 'Bild',
		'url' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
		'label' => 'SMW imported image1'),
		array(
		'type'  => 'Bild',
		'url' => 'http://zeitgeist.yopi.de/wp-content/uploads/2007/12/wordpress.png',
		'label' => 'SMW imported image2')
	);
	return $data;
  }

  static function get_galleries(){
	$data = array( array(
		'type'  => 'Gallery',
		'name' => 'Test gallery1',
		'description' => 'An imported test gallery',
		'gallery_folder' => '/testfolder1'),
		array(
		'type'  => 'Gallery',
		'name' => 'Test gallery2',
		'description' => 'Another imported test gallery',
		'gallery_folder' => '/testfolder2')
	);
	return $data;
  }

  public static function get_sources(){
	$sources = array(
		array(smwimport_test,get_events),
		array(smwimport_test,get_press),
		array(smwimport_test,get_images),
		array(smwimport_test,get_links),
		array(smwimport_test,get_galleries)
	);
	return $sources;
  }

}

?>
