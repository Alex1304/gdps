<?php

namespace App\Services;

class TimeFormatter
{
	public function format(\DateTime $datetime)
	{
		$now = new \DateTime();
		$interval = $now->diff($datetime);
		$diffSecs = $now->getTimestamp() - $datetime->getTimestamp();

		$result = 'Unknown';

		if ($diffSecs < 60)
			$result = $diffSecs . ' second' . ($diffSecs > 1 ? 's' : '');

		if ($diffSecs >= 60) {
			$val = $interval->format('%i');
			$result = $interval->format('%i minute' . ($val > 1 ? 's' : ''));
		}

		if ($diffSecs >= 3600) {
			$val = $interval->format('%h');
			$result = $interval->format('%h hour' . ($val > 1 ? 's' : ''));
		}

		if ($diffSecs >= 86400) {
			$val = $interval->format('%d');
			$result = $interval->format('%d day' . ($val > 1 ? 's' : ''));
		}

		if ($diffSecs >= 604800) {
			$val = intval($interval->format('%d') / 7);
			$result = $val . ' week' . ($val > 1 ? 's' : '');
		}

		if ($interval->format('%m') > 0 && $interval->format('%y') == 0) {
			$val = $interval->format('%m');
			$result = $interval->format('%m month' . ($val > 1 ? 's' : ''));
		}

		if ($interval->format('%y') > 0) {
			$val = $interval->format('%y');
			$result = $interval->format('%y year' . ($val > 1 ? 's' : ''));
		}

		return $result;
	}
}