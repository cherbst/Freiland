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
		'subtitle' => 'Album release concert',
		'eventtype'  => 'Party',
		'date_begin' => '2011-06-02 10:00',
		'short_description' => 'Rebellion der Träumer',
		'long_description' => 'Das neue Rebellion der Träumer Konzert!',
		'genre' => 'rock',
		'homepage' => array( 'www.test1.de','www.test2.de','www.test3.de'),
		'location' => 'Werkstatt',
		'house' => 'big house',
		'room' => '203',
		'age' => '18',
		'banner' => array('url' => 'http://87.238.194.42/cmsbilder/Kollektiv-Turmstrasse.jpg',
				'title' => 'Turmstrasse')
		);
	$data[] = array(
		'type'  => 'Veranstaltung',
		'label' => 'Matthew Herbert',
		'title' => 'Matthew Herbert',
		'eventtype'  => 'Konzert',
		'date_begin' => '2011-06-02 10:00',
		'short_description' => 'Matthew Herbert',
		'long_description' => 'Wenn es jemand gibt, der, ganz englisch, indeed very sophisticated ist,
dann ist das ohne Zweifel Matthew Herbert. In vielerlei Hinsicht. Nicht
nur was sein Schaffen in und für die elektronische Musik angeht, auch
sein politisches Engagement erscheint beachtenswert. 

Matthew Herbert ist vier Jahre alt, als er zum ersten Mal in den Genuss
von Geigen- bzw. Klavierunterricht kommt. Diesem frönt er bis zum Be-
ginn seines Studiums der Theaterwissenschaft. Während der Schule spielt
Herbert in Orchestern, nebenher singt er auch im Schülerchor und mit
13 gibt er den Keyboarder in verschiedenen Bands. In seiner Schulzeit
bekommt Herbert es mit dem Musiklehrer Pete Stollery zu tun, der den
musikalischen Denkhorizont seiner Schüler, insbesondere Matthew Her-
bert, erweitert, indem er Jazz, wie auch die Musik von Reich oder Xenakis
gleichberechtigt mit Klassik (z.B. Beethoven) behandelt.

Matthew Herbert ist vier Jahre alt, als er zum ersten Mal in den Genuss
von Geigen- bzw. Klavierunterricht kommt. Diesem frönt er bis zum Be-
ginn seines Studiums der Theaterwissenschaft. Während der Schule spielt
Herbert in Orchestern, nebenher singt er auch im Schülerchor und mit
13 gibt er den Keyboarder in verschiedenen Bands. In seiner Schulzeit
bekommt Herbert es mit dem Musiklehrer Pete Stollery zu tun, der den
musikalischen Denkhorizont seiner Schüler, insbesondere Matthew Her-
bert, erweitert, indem er Jazz, wie auch die Musik von Reich oder Xenakis
gleichberechtigt mit Klassik (z.B. Beethoven) behandelt.
',
		'genre' => 'rock',
		'homepage' => array( 'www.matthewherbert.com'),
		'location' => 'Spartacus',
		'house' => 'big house',
		'room' => '203',
		'age' => '18',
		'image_big' => array('url' => 'http://87.238.194.42/cmsbilder/matthew_herbert.jpg',
				     'title' => 'Matthew Herbert'),
		'sponsor' => array(
			  array('url' => 'http://87.238.194.42/cmsbilder/sponsor_stadtwerke.gif',
			        'title' => 'Stadtwerke Potsdam'),
			  array('url' => 'http://87.238.194.42/cmsbilder/sponsor_page.jpg',
			        'title' => 'Page')
			)
		);
	$data[] = array(
		'type'  => 'Veranstaltung',
		'label' => 'ULTRASH',
		'title' => 'ULTRASH - FESTIVAL V',
		'eventtype'  => 'Festival',
		'date_begin' => '2011-05-20 10:00',
		'date_end' => '2011-05-21 10:00',
		'long_description' => '
Am 20./21.5.2011 wird das mittlerweile fünfte Ultrash-Festival in Zusam-
menarbeit von RASH Berlin-Brandenburg , Scortesi , dem Filmstadtinferno
99 , Stara Garda und weiteren Unterstützer_innen in Potsdam in der neu-
en Location „Freiland“ stattfinden. Folgende Bands werden aufteten:
The Offenders (Ska/Italien/Berlin)

United Struggle (AFA-Oi!/Düsseldorf)
Redska (Ska/Italien)
Jesus Skins (Christlicher-AFA-Oi!)
Bier Iki Ütsch (Punk/Potsdam)
The Last Minute (Ska/Ungarn)
Produzenten der Froide (AFA-Oi!/Stuttgart)
The Bayonets (AFA – Stretpunk/Serbien)
Lea-Won (Hip Hop/München)
und Asi & Jaycop (Hip Hop/Potsdam)',
		'genre' => 'rock',
		'homepage' => array( 'http://ultrash.blogsport.de/vorankuendigung-2011/'),
		'location' => 'Freiland',
		'house' => 'big house',
		'room' => '203',
		'age' => '18',
		'image_big' => array('url' => 'http://87.238.194.42/cmsbilder/ultrash_v.jpg',
				     'title' => 'Ultrash V'),
		'sponsor' => array(
			  array('url' => 'http://87.238.194.42/cmsbilder/antifalogo.gif',
			        'title' => 'Antifa'),
			  array('url' => 'http://87.238.194.42/cmsbilder/scortel_logo.jpg',
			        'title' => 'Scortel'),
			  array('url' => 'http://87.238.194.42/cmsbilder/asta_07.png',
			        'title' => 'Asta')
			)
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

  static function get_featured_image($i){
	switch($i){
	case 1:  return 'fest10_kl.jpg';
	case 2:  return 'fl18_kl.jpg';
	case 3:  return 'fest2_kl.jpg';
	case 4:  return 'fl4_kl.jpg';
	case 5:  return 'fl5_kl.jpg';
	case 6:  return 'fl38_kl.jpg';
	case 7:  return 'fl7_kl.jpg';
	case 8:  return 'fl17_kl.jpg';
	case 9:  return 'fl42_kl.jpg';
	case 10:  return 'fl9_kl.jpg';
	case 11:  return 'fl8_kl.jpg';
	case 12:  return 'fl20_kl.jpg';
	case 13:  return 'fl52_kl.jpg';
	case 14:  return 'dnb35_klein.jpg';
	case 15:  return 'hae6_klein.jpg';
	case 16:  return 'fest_feuer1_kl.jpg';
	case 17:  return 'fb48_kl.jpg';
	case 18:  return 'blumenfrau.jpg';
	case 19:  return 'fb10_kl.jpg';
	case 20:  return 'fbo14_kl.jpg';
	case 21:  return 'fl16_kl.jpg';
	default : return ''; 
	}
  }

  static function get_galleries(){
	for ( $i=1;$i<=21;$i++)
		$data[] = array(
		'type'  => 'Gallery',
		'name' => 'Test gallery'.$i,
		'description' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aene-
an commodo ligula eget dolor. Aenean massa. Cum sociis natoque
penatibus et magnis dis parturient montes, nascetur ridiculus mus.
Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem.
Nulla consequat massa quis enim. Donec pede justo, fringilla vel,
aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imper-
diet a, venenatis vitae, justo.
'.$i,
		'gallery_folder' => dirname(__FILE__) . '/../../themes/freiland/gallery',
		'featured_image' => self::get_featured_image($i) );

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
