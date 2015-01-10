<?php

/*
 *      benchmark-concurrent.test.php
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
	
	$key; 
	$pathCacheFile;
	
	use EC\Storage\Cache as Cache;						
								
	function initVar() {
		
		global $key;
		
		$key = 'test';
		
		$pathCacheDir = dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cache";
		
		Cache::configure(array(
			'cache_path' => $pathCacheDir,
			'expires' => 180
			));
		
	}

	function preTest() {
		
		global $key, $pathCacheFile, $numThread, $nameKeyThread;	
		
		initVar();
		
		$numThread = 5;
		$nameKeyThread = 'res';
		
		
		$pathCacheFile = Cache::get_route( $key );
		
		assert_options(ASSERT_ACTIVE, 1);
		assert_options(ASSERT_WARNING, 0);
		assert_options(ASSERT_QUIET_EVAL, 1);
		assert_options(ASSERT_CALLBACK, 'assertHandler');		
		
		if ( is_file( $pathCacheFile )  )  {
			
			@unlink( $pathCacheFile);
			
		}
		
		deleteValuesResultThread( $nameKeyThread, $numThread ) ;
		
	}
	
	function postTest() {
		
		global $pathCacheFile, $numThread, $nameKeyThread;	
		

		if ( is_file( $pathCacheFile )  )  {
			
			@unlink( $pathCacheFile);
			
		}
		
		deleteValuesResultThread( $nameKeyThread, $numThread ) ;
		
	}
	
	function readThread( $keyThread ) {
		
		global $key;
		
		for ( $i = 0 ; $i < 500 ; $i++ ) {
			
			$valueCache = Cache::get( $key );
			
			if ( ! isset( $valueCache ) ) {
			
				throw new Exception( "Error to threat get valueCache $keyThread" );
				
			}
			
		}
		
		Cache::put( $keyThread, true );		
		
	} 
	
	function benchmarkGet( $sizeList, $sizeObject ) {
		
		global $key, $pathCacheFile, $numThread, $nameKeyThread;
		
		$res = true;
		
		$stat = array();		
		$statControl = array();
		
		$valueRandom = generateValueRandom( $sizeList, $sizeObject );
		
		$valueRandomControl = createValueControl( $valueRandom );
		
		$resPut =  Cache::put( $key, $valueRandom );
		
		$res = assert( '$resPut' ) && $res;	

		runThreads( __FILE__ , $nameKeyThread, $numThread );
		
		for ( $i = 0 ; $i < 500 ; $i++ ) {
			
			$time1 = microtime( true ) ;						
			$valueCache = Cache::get( $key );			
			$stat = saveStat( $time1, $stat );
			
			$res = assert( 'is_file( $pathCacheFile )' )         && $res;	
			$res = assert( '$valueRandom == $valueCache' )       && $res;	
			
			$time1 = microtime( true ) ;
			$valueCache = testControl( $valueRandomControl );
			$statControl = saveStat( $time1, $statControl );
			
			$res = assert( '$valueRandom == $valueCache' ) && $res;		
			
		}
		
		waitEndThread( "res" , $numThread );
		
		if ( $res ) {
			
			printStat( "\n== Cache::get ====",  $stat, $sizeList, $sizeObject, $numThread + 1   );
			printStat( " ==> Value control memory", $statControl, $sizeList, $sizeObject, $numThread + 1 );
						
		}
		
	}
	
	function waitEndThread( $nameKeyThread, $numThread  ) {
		
		$res = false;
		
		for( $ii = 0; $ii < 45 ; $ii++ ) {
			
			$res = true;
			
			for( $i = 0; $i < $numThread; $i++ ) {
				
				$result = Cache::get( "$nameKeyThread$i" );				
				
				$res = isset( $result );
				
				if ( !$res ) {
					
					break;
					
				}
				
			}
			
			if ( $res ) {
			
				break;
				
			}
			
			sleep(1);
			
		}
		
	}
	
	/******************************/ /******************************/
	/******************************/ /******************************/
	/******************************/ /******************************/
	
	if( isset( $argv[1] ) ) {
		
		initVar(); readThread( $argv[1] );		
		
		
	} else {
	
		if ( ! isset( $argv[0] ) )  echo "<pre> \n\n";
		
		preTest(); benchmarkGet( 100, 20 ) ; postTest();	
		
		if ( ! isset( $argv[0] ) )  echo "\n</pre> \n";
		
	}
	 
	
?>
