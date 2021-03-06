<?php

namespace Report\Api;

use Symfony\Component\HttpFoundation\ParameterBag;

class StatementBuilder
{
	public static function build(ParameterBag $parameters) {
		$statementBuilder = new \StatementBuilder();
		$statements = [];
		$i = 1;
		foreach ($parameters->get('filters') as $type => $value) {
			if ($value === '') {
				continue;
			}
			$filter = ReportService::DIMENSION_MAPPING[$type];
			if (is_array( $value ) && !empty($value["not"])) {
				$statements[] = self::buildNotStatement($statementBuilder, $i, $filter, $type, $value['not']);
			} else {
				$statements[] = self::buildStatement($statementBuilder, $i, $filter, $type, $value);
			}
			$i++;
		}
		$statementBuilder->Where(implode(' and ', $statements));

		return $statementBuilder->ToStatement();
	}

	private static function buildStatement($statementBuilder, $index, $filter, $type, $values) {
		if (!is_array($values)) {
			$values = explode(',', $values);
		}
		$keys = [];
		$i = 1;
		foreach ($values as $value) {
			$key = sprintf('%s_%d_%d', $type, $index, $i);
			$keys[] = ':' . $key;
			$statementBuilder->WithBindVariableValue($key, self::parseValue($type, trim($value)));
			$i++;
		}

		return sprintf('%s in (%s)', $filter, implode(',', $keys));
	}

	private static function parseValue($type, $value) {
		if (strpos($type, 'Id') !== false || strpos($type, '_id') !== false) {
			$value = intval($value);
		}

		return $value;
	}

	/**
	 * @param $statementBuilder
	 * @param $index
	 * @param $filter
	 * @param $type
	 * @param $values
	 * @return string
	 *
	 * @TODO currently there is no support for multiple values in not statement
	 */
	private static function buildNotStatement( $statementBuilder, $index, $filter, $type, $values ) {
		$value = is_array($values) ? $values[0] : $values;
		$key = sprintf('%s_%d_%d', $type, $index, 1);
		$keyFormatted = ':' . $key;

		$statementBuilder->WithBindVariableValue($key, self::parseValue($type, trim($value)));

		return sprintf('%s != %s', $filter, $keyFormatted);
	}
}
