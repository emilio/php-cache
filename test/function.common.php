<?
/*
 *      function.common.php
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

	use EC\Storage\Cache as Cache;
	
	function assertHandler($file, $line, $code ) {
		
		echo ":: ERROR :::\n";
		echo "Assertion failed at $file:$line";
		 
		echo "\t$code\n";
		
	}
	
	function runThreads( $filePHP, $nameKeyThread, $numThreat ) {
		
		for ( $i = 0 ; $i < $numThreat ; $i++ ) {
			
			exec( "php -f  $filePHP $nameKeyThread$i  > /dev/null  & " );
/*
			exec( "php -f  $filePHP $nameKeyThread$i  > $nameKeyThread$i.log  & " );
*/
			
			echo "   ... Running thread $i\n";
		
		}		
		
	}
	
	function deleteValuesResultThread( $nameKeyThread, $numThread ) {
		
		for( $i = 0; $i <= $numThread; $i++ ) {
			
			Cache::delete( "$nameKeyThread$i" );
		
		}
		
	}
	
	function generateValueRandom( $sizeList, $sizeObject ) {
		
		$value = array();
		
		for ( $i = 0; $i < $sizeList ; $i++ ) {
			
			$objectRandom = array();
			
			for ( $j = 0; $j < $sizeObject; $j++ ) {
				
				$random = uniqid("logicaalternativa_" , true). uniqid("_" , true)."?>";
				
				$objectRandom["logicaalternativa_$j"] = $random;
				
			}			
			
			$value[] = $objectRandom;
			
		}
		
		return $value;
		
	}
	
	function saveStat( $time1, $stat ){
		
		$time2 = microtime( true );
		
		$miliSeg = round( ( $time2 - $time1 ) * 1000, 1 );
		
		$stat[] = $miliSeg;
		
		return $stat;
		
	}
	
	function printStat( $text, $stat, $sizeList, $sizeObject ) {
		
		$sum = 0;
		
		foreach( $stat as $value ) {
			
			$sum = $sum + $value;			
			
		}
		
		$count = count( $stat );
		
		$average = round( $sum / $count, 1 );
		
		echo "$text\n ";
		echo "Object: sizeList $sizeList (size each object: $sizeObject) \n";
		echo " Count request: $count, average: $average miliseg\n\n";
		
	}
	
	function testControl ( $value ) {
		
		return unserialize( $value );		
		
	}
	
	function createValueControl( $valueRandom ){
		
		return serialize( $valueRandom ) ;
		
	}
	
	function execThreadPutValue( $key, $value1, $value2, $value3, $i ) {
		
		$valuePut = $value1;
		
		$j = $i % 3;
			
		if ( $j == 0 ) {
			
			$valuePut = $value3;
			
		} elseif ( $j == 2 ) {
			
			$valuePut = $value2;
			
		}
		
		$resPut = Cache::put( $key, $valuePut );
		
		return $resPut ? 0 : 1;
		
	}
	
?>
