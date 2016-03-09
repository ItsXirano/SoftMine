<?php

namespace softmine\math;

abstract class Math{

	public static function floorFloat($n){
		$math = (int) $n;
		return $n >= $math ? $math : $math - 1;
	}

	public static function ceilFloat($n){
		$math = (int) ($n + 1);
		return $n >= $math ? $math : $math - 1;
	}
}
