<?php
/*
 *      robustness-rw2.test.php
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
	$keyValue1;	$keyValue2; $keyValue3;	
	$nameKeyThread; $numThread; $numRequest;
	
	use EC\Storage\Cache as Cache;
	
	function initVar() {
		
		global $keyValue1, $keyValue2, $keyValue3, $key, $numRequest;
		
		$key = "test";
		
		$numRequest = 50;
		
		$keyValue1 = "IOvalue1";
		$keyValue2 = "IOvalue2";
		$keyValue3 = "IOvalue3";
		
		Cache::configure(array(
			'cache_path' => dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."cache",
			'expires' => 180
			));
		
	}

	function preTest() {
		
		global $key, $keyValue1, $keyValue2, $keyValue3, $nameKeyThread, $numThread;	
		
		initVar();
		
		$numThread = 10;
		$nameKeyThread = 'res';
		
		assert_options(ASSERT_ACTIVE, 1);
		assert_options(ASSERT_WARNING, 0);
		assert_options(ASSERT_QUIET_EVAL, 1);
		assert_options(ASSERT_CALLBACK, 'assertHandler');
		
		
		$value1 = generateValueRandom( 100, 20 );
		
		Cache::put( $keyValue1, $value1 );
		Cache::put( $keyValue2, generateValueRandom( 100, 20 ) );
		Cache::put( $keyValue3, generateValueRandom( 100, 20 ) );
		
		Cache::put( $key, $value1 );
		
		deleteValuesResultThread($nameKeyThread, $numThread );
		
	}
	
	function postTest() {
		
		global $key, $keyValue1, $keyValue2, $keyValue3, $nameKeyThread, $numThread;	
		
		Cache::delete( $key );
		
		Cache::delete( $keyValue1 );
		Cache::delete( $keyValue2 );
		Cache::delete( $keyValue3 );	
		
		deleteValuesResultThread( $nameKeyThread, $numThread );
		
	}
	
	function execThread( $keyThread ) {
		
		global $keyValue1, $keyValue2, $keyValue3, $key, $numRequest;
		
		$result = createResultInit();
		
		$value1 = Cache::get( $keyValue1 );
		$value2 = Cache::get( $keyValue2 );
		$value3 = Cache::get( $keyValue3 );
		
		for ( $i = 0 ; $i < $numRequest ; $i++ ) {
			
			$result["get"] += execThreadGetValue( $key, $value1, $value2, $value3 ) ;
			
			$result["put"] += execThreadPutValue( $key, $value1, $value2, $value3, $i ) ;
			
			Cache::delete( $key );
			
		}
		
		Cache::put( $keyThread, $result );		
		
	} 
	
	function execThreadGetValue( $key, $value1, $value2, $value3 ){
		
		$valueCache = Cache::get( $key );
		
		$res = ! isset( $valueCache )
					|| $valueCache == $value1
					|| $valueCache == $value2
					|| $valueCache == $value3
					;
		
		return $res ? 0 : 1;
		
	}
	
	function benchmarkIO() {
		
		global $nameKeyThread, $numThread , $numRequest;
		
		runThreads( __FILE__ , $nameKeyThread, $numThread );
		
		$allStatResult = waitEndThread( $nameKeyThread, $numThread );
		
		echo "\n:: STATISTICS :: \n";
		echo "Number request: ". ( $numThread * $numRequest ) ." \n";
		echo " ==> Get request error ::\n";
		echo "  Invalid value: {$allStatResult['get']}, it must be 0\n";
		echo " ==> Put request error :: \n";
		echo "  Error to put value: {$allStatResult['put']}, it must be 0\n\n";
		
	}
	
	function createResultInit() {
		
		$result["get"] = 0;
		$result["put"] = 0;
		
		return $result;
		
	}
	
	function waitEndThread( $nameKeyThread, $numThread  ) {
		
		$res = false;
		
		$allStatResult = array();
		
		for( $ii = 0; $ii < (45 * $numThread )  ; $ii++ ) {
			
			$res = true;
			
			$allStatResult = createResultInit();
			
			for( $i = 0; $i < $numThread; $i++ ) {
				
				$result = Cache::get( "$nameKeyThread$i" );				
				
				$res = isset( $result );
				
				if ( !$res ) {
					
					break;
					
				} else {
					
					$allStatResult["get"] += $result["get"];
					$allStatResult["put"] += $result["put"];
					
				}
				
			}
			
			if ( $res ) {
			
				break;
				
			}
			
			sleep(1);
			
		}
		
		if ( ! $res ) {
			
			throw new Exception('Error to end all thread');
			
		}
		
		return $allStatResult;	
		
	}
	
	/******************************/ /******************************/
	/******************************/ /******************************/
	/******************************/ /******************************/
	
	if( isset( $argv[1] ) ) {
		
		initVar(); execThread( $argv[1] );		
		
		
	} else {
	
		if ( ! isset( $argv[0] ) )  echo  "<pre> \n\n";
		
		preTest(); benchmarkIO() ; postTest();
		
		if ( ! isset( $argv[0] ) )  echo "\n</pre> \n";
		
	}
	 
	
?>
