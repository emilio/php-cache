<?php
/*
 *      benchmark.test.php
 *      
 *      Copyright 2015 Miguel Rafael Esteban Martín (www.logicaalternativa.com) <miguel.esteban@logicaalternativa.com>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */	
	
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."Cache.php";
	require_once dirname(__FILE__).DIRECTORY_SEPARATOR."function.common.php";
	
	$pathCacheFile; 
	$key; 
	$pathCacheFile;
	
	use EC\Storage\Cache as Cache;			
								
	function preTest() {
		
		global $key, $pathCacheFile;	
		
		$key = 'test';
		
		
		
		Cache::configure(array(
			'cache_path' => dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cache",
			'expires' => 180
			));
		
		
		$pathCacheFile = Cache::get_route( $key );
		
		assert_options(ASSERT_ACTIVE, 1);
		assert_options(ASSERT_WARNING, 0);
		assert_options(ASSERT_QUIET_EVAL, 1);
		assert_options(ASSERT_CALLBACK, 'assertHandler');		
		
		if ( is_file( $pathCacheFile )  )  {
			
			@unlink( $pathCacheFile);
			
		}
		
		
		
	}
	
	function postTest() {
		
		global $pathCacheFile;

		if ( is_file( $pathCacheFile )  )  {
			
			@unlink( $pathCacheFile);
			
		}
		
	}
	
	function benchmarkGet( $sizeList, $sizeObject ) {
		
		global $key, $pathCacheFile, $pathCacheFileTemp;
		
		$stat = array();
		$statControl = array();
		
		$res = true;
		
		$valueRandom = generateValueRandom( $sizeList, $sizeObject );
		
		$valueRandomControl = createValueControl( $valueRandom );
		
		$resPut =  Cache::put( $key, $valueRandom );
		
		$res = assert( '$resPut' )  && $res;	
		
		for ( $i = 0 ; $i < 500 ; $i++ ) {
			
			$time1 = microtime( true ) ;						
			$valueCache = Cache::get( $key );			
			$stat = saveStat( $time1, $stat );
			
			$res = assert( '$valueRandom == $valueCache' )   && $res;	
			
			$time1 = microtime( true ) ;
			$valueCache = testControl( $valueRandomControl );
			$statControl = saveStat( $time1, $statControl );
			
			$res = assert( '$valueRandom == $valueCache' ) && $res;
		
		}
		
		if ( $res ) {
		
			printStat( "\n== Cache::get ====",  $stat, $sizeList, $sizeObject );
			printStat( " >> Value control memory", $statControl, $sizeList, $sizeObject );
			
		}
		
	}
	
	
	if ( ! isset( $argv[0] ) )  echo "<pre> \n\n";
	
	preTest(); benchmarkGet( 1, 5 )    ; postTest();
	preTest(); benchmarkGet( 1, 100 )  ; postTest();
	preTest(); benchmarkGet( 50, 5 )  ; postTest();
	preTest(); benchmarkGet( 50, 10 ) ; postTest();
	preTest(); benchmarkGet( 50, 20 ) ; postTest();
	preTest(); benchmarkGet( 100, 5 )  ; postTest();
	preTest(); benchmarkGet( 100, 10 ) ; postTest();
	preTest(); benchmarkGet( 100, 20 ) ; postTest();
	
	if ( ! isset( $argv[0] ) )  echo  "\n</pre> \n";
	
?>
