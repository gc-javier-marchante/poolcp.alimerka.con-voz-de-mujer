<?php

namespace Alimerka;

class PromotionalCode
{
	/**
	 * Promotional code
	 * 
	 * @var string
	 */
	private $code = null;

	/**
	 * Constructor
	 *
	 * @param string $code
	 */
	public function __construct(string $code)
	{
		$this->validSupermarketCodes = array_values(\MySQLModel::getInstance('store')->getList('code', 'code'));
		$this->code = $code;
	}

	public function generateValidRandomCode()
	{
		$code = '93';

		while (strlen($code) < 16) {
			$code .= rand(0, 9);
		}

		$code = substr($code, 0, 2) . $this->validSupermarketCodes[array_rand($this->validSupermarketCodes)] . substr($code, 5);
		$code = substr($code, 0, 12) . $this->validPromotionCodes[array_rand($this->validPromotionCodes)] . substr($code, 16);
		$code = $code . $this->calculateControlDigit($code);

		return $code;
	}

	private $validSupermarketCodes = [
	];

	private $validPromotionCodes = [
		'1120',
	];

	/**
	 * Check if code is valid
	 *
	 * @return bool
	 */
	public function isValid(): bool
	{
		if (strlen($this->code) != 18) {
			return false;
		}

		if (!starts_with($this->code, '93')) {
			return false;
		}

		if (!in_array($this->getSupermarket(false), $this->validSupermarketCodes)) {
			//return false;
		}

		if (!in_array($this->getPromotionCode(false), $this->validPromotionCodes)) {
			return false;
		}

		if (substr($this->code, -2, 2) != $this->calculateControlDigit(substr($this->code, 0, -2))) {
			return false;
		}

		return true;
	}

	/**
	 * Returns calculated control digit
	 *
	 * @param string $ean
	 * @return string
	 */
	private function calculateControlDigit(string $ean): string
	{
		//Function Mod97(Num As String) As String
		//    Dim lngTemp As Long
		//    Dim strTemp As String
		//    Dim data As String
		//   
		//    data = Num
		// 
		//    Do While Val(Num) >= 97
		//        If Len(Num) > 5 Then
		//            strTemp = Left(Num, 5)
		//            Num = Right(Num, Len(Num) - 5)
		//        Else
		//            strTemp = Num
		//            Num = ""
		//        End If
		//       
		//        lngTemp = CLng(strTemp)
		//        lngTemp = lngTemp Mod 97
		//        strTemp = CStr(lngTemp)
		//        Num = strTemp & Num
		//    Loop
		//   
		//    Mod97 = Format(97 - CInt(Num),"00")
		// 
		//End Function

		$Num = $ean;

		$lngTemp = null;
		$strTemp = null;

		while (intval($Num) >= 97) {
			if (strlen($Num) > 5) {
				$strTemp = substr($Num, 0, 5);
				$Num = substr($Num, 5);
			} else {
				$strTemp = $Num;
				$Num = '';
			}

			$lngTemp = intval($strTemp);
			$lngTemp = $lngTemp % 97;
			$strTemp = strval($lngTemp);
			$Num = $strTemp . $Num;
		}

		$control = str_pad(abs(97 - intval($Num) - 97), 2, '0', STR_PAD_LEFT);

		return $control;
	}

	/**
	 * Returns supermarket code
	 *
	 * @param bool $check_validity
	 * @return string|null
	 */
	public function getSupermarket($check_validity = true): ?string
	{
		if (
			$check_validity
			&& !$this->isValid()
		) {
			return null;
		}

		return substr($this->code, 2, 3);
	}

	/**
	 * Check if is of supermarket
	 *
	 * @param string $supermarket_code
	 * @return boolean
	 */
	public function isOfSupermarket(string $supermarket_code): bool
	{
		if (strlen($supermarket_code) != 3) {
			return false;
		}

		return $supermarket_code == $this->getSupermarket();
	}

	/**
	 * Returns promotion code
	 *
	 * @param bool $check_validity
	 * @return string|null
	 */
	public function getPromotionCode($check_validity = true): ?string
	{
		if (
			$check_validity
			&& !$this->isValid()
		) {
			return null;
		}

		return substr($this->code, 12, 4);
	}
}
