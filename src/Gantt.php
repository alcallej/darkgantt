<?php


namespace Dark\Dummy\Gantt;

use Dark\Dummy\Date\DarkDummyDate;

/**
 * 
 */
define('LANG_DIR', __DIR__ . '/lang/');

class Gantt
{
	protected $data = array();
	protected $_years = array();
	protected $default_locale = 'es';
	protected $config = array();

	protected $locale_array = null;

	const MONTHS_FULLNAME = 0;
	const MONTHS_SHORTNAME = 1;
	const MONTHS_ABBREVIATION = 2;

	const YEAR_COLSPAN = 12;
	const MONTHS_COLSPAN = 1;


	function __construct($data, $params = array())
	{
		$defaults = array(
			'months_type_name' => self::MONTHS_ABBREVIATION,
			'title' => false,
			'title_events' => false
		);
		$this->data = $data;
		$this->config = array_merge($defaults, $params);
		$this->parse();
	}

	public function setLocale($locale)
	{
		$this->default_locale = $locale;
		$this->parse();
	}

	public function getLocale()
	{
		return $this->default_locale;
	}

	function parse()
	{
		$this->_years = array();
		$this->getYears();
		$this->locale_array = require LANG_DIR . $this->default_locale . '.php';
	}

	function getDateData($start_date, $end_date)
	{
		$date1 = DarkDummyDate::parse($start_date);
		$date2 = DarkDummyDate::parse($end_date);
		return array(
			'color_task' => $this->doNewColor(),
			'weeks' => $date1->diffInWeeks($date2)
		);
	}

	function doNewColor()
	{
		$color = str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT);
		return '#' . $color;
	}

	function __toString()
	{
		return $this->render();
	}

	function render()
	{
		$dates_events = [];
		foreach ($this->data as $event) {
			$temp = $this->getDateData($event['start'], $event['end']);
			$temp['task_name'] = $event['label'];
			$temp['start'] = $event['start'];
			$temp['end'] = $event['end'];
			$temp['years'] = $this->bt_years_counter($event);
			$dates_events[] = $temp;
		}

		$html = [];
		$html[] = '<div style="overflow-x:auto;overflow-y:auto;">';
		$html[] = '<table class="tg" style="undefined;table-layout: fixed; height:40%;">';
		$html[] = '<tr>';
		$html[] = '<th class="tg-uys7" colspan="2" rowspan="3" style="width: 450px;">' . $this->locale_array['title_events'] . '</th>';
		$html[] = '<th class="tg-uys7" colspan="' . self::YEAR_COLSPAN * count($this->_years) . '" >' . $this->locale_array['title'] . '</th>';
		$html[] = '</tr>';
		$html[] = '<tr>';
		foreach ($this->_years as $year) {
			$html[] = '<td class="tg-aj9k" colspan="' . self::YEAR_COLSPAN . '" >' . $year . '</td>';
		}
		$html[] = '</tr>';
		$html[] = '<tr>';
		for ($i = 0; $i < count($this->_years); $i++) {
			foreach ($this->locale_array['months_short'] as $month) {
				$m = ($this->config['months_type_name'] == self::MONTHS_ABBREVIATION) ? strtoupper(substr($month, 0, 1)) : $month;
				$html[] = '<td class="tg-ly6r">' . $m . '</td>';
			}
		}
		$html[] = '</tr>';
		$cont = 0;
		foreach ($dates_events as $date_event) {
			$tooltip = [];
			$tooltip[] = 'Actividad: ' . $date_event['task_name'];
			$tooltip[] = 'Duracion: ' . $date_event['weeks'] . ' semanas';
			$tooltip[] = 'Fecha de Inicio: ' . DarkDummyDate::parse($date_event['start'])->format('d/m/Y');
			$tooltip[] = 'Fecha de Finalizacion: ' . DarkDummyDate::parse($date_event['end'])->format('d/m/Y');
			$html[] = '<tr title="' . implode("\n", $tooltip) . '">';
			$html[] = '<td colspan="2" class="tg-yw4l" style="max-width: 250px;">' . $date_event['task_name'] . '</td>';
			$start = DarkDummyDate::parse($date_event['start']);
			$end = DarkDummyDate::parse($date_event['end']);
			for ($j = 0; $j < count($this->_years); $j++) {
				for ($i = 1; $i <= self::YEAR_COLSPAN; $i++) {

					if ($i == $start->month && $this->_years[$j] == $start->year) {
						$s = $start->day;
					} elseif ($i == $end->month && $this->_years[$j] == $end->year) {
						$s = $end->day;
					} else {
						$s = 1;
					}

					$temp_date = DarkDummyDate::createFromFormat('Y/m/d', '' . $this->_years[$j] . '/' . $i . '/' . $s);
					if ($temp_date->between($start, $end)) {
						$class = '';
						if ($temp_date->eq($start)) {
							$class = 'taskbar-start';
						} elseif ($temp_date->eq($end)) {
							$class = 'taskbar-end';
						} else {
							$class = 'taskbar';
						}
						$html[] = '<td colspan="' . self::MONTHS_COLSPAN . '" class="tg-yw4l ' . $class . '" style="width:' . round((count($this->_years) / self::YEAR_COLSPAN), 2) . '%; padding:0;"><div style="background: ' . $date_event['color_task'] . ' !important; display:inline-block;height:100%; width:100%;position:relative;"></div></td>';
					} else {
						$html[] = '<td colspan="' . self::MONTHS_COLSPAN . '" class="tg-yw4l" style="width:' . round((count($this->_years) / self::YEAR_COLSPAN), 2) . '%;"><div></div></td>';
					}
				}
			}
			$html[] = '</tr>';
			$cont++;
		}
		/*end*/

		$html[] = '</table></div>';

		return implode('', $html);
	}

	function bt_years_counter($event)
	{
		$temp_date =  DarkDummyDate::parse(DarkDummyDate::parse($event['start'])->year . '/01/01');
		$end_date =  DarkDummyDate::parse($event['end']);
		$counter = 0;
		while (true) {
			if ($temp_date->year < $end_date->year) {
				$counter++;
				$temp_date->addYear(1);
			} else {
				break;
			}
		}
		return $counter;
	}

	function getYears()
	{
		$first_event_date = $this->data[0];
		$this->_years[0] = DarkDummyDate::parse($first_event_date['start'])->year;
		if (DarkDummyDate::parse($first_event_date['end'])->year != $this->_years[0])
			$this->_years[] = DarkDummyDate::parse($first_event_date['end'])->year;
		for ($i = 0; $i < count($this->data); $i++) {
			$temp = $this->data[$i];
			$temp_date = DarkDummyDate::parse($temp['start']);
			$end_date = DarkDummyDate::parse($temp['end']);
			$finish = false;
			while (!$finish) {
				if ($temp_date->year <= $end_date->year) {
					if (!in_array($temp_date->year, $this->_years, true)) {
						$this->_years[] = $temp_date->year;
					}
					$temp_date->addYear(1);
				} else {
					$finish = true;
				}
			}
		}
		sort($this->_years, SORT_NUMERIC);
	}
}
