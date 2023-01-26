<?php

use Alimerka\PromotionalCode;

/**
 * Class Participant
 *
 * Model class for table participants
 *
 * <MAGIC_METHODS>
 * @method array|object getById(int $id, array $options = [])
 * @method array getAllById(int $id, array $options = [])
 * @method array getAnyById(array $listOfIds, array $options = [])
 * @method bool deleteAllById(int $id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyById(array $listOfIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateById(int $id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllById(int $id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyById(array $listOfIds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByCode(int $code, array $options = [])
 * @method array getAllByCode(int $code, array $options = [])
 * @method array getAnyByCode(array $listOfCodes, array $options = [])
 * @method bool deleteByCode(int $code, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByCode(int $code, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByCode(array $listOfCodes, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByCode(int $code, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByCode(int $code, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByCode(array $listOfCodes, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByCodeSupermarket(int $code_supermarket, array $options = [])
 * @method array getAllByCodeSupermarket(int $code_supermarket, array $options = [])
 * @method array getAnyByCodeSupermarket(array $listOfCodeSupermarkets, array $options = [])
 * @method bool deleteByCodeSupermarket(int $code_supermarket, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByCodeSupermarket(int $code_supermarket, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByCodeSupermarket(array $listOfCodeSupermarkets, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByCodeSupermarket(int $code_supermarket, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByCodeSupermarket(int $code_supermarket, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByCodeSupermarket(array $listOfCodeSupermarkets, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByCodePromotion(int $code_promotion, array $options = [])
 * @method array getAllByCodePromotion(int $code_promotion, array $options = [])
 * @method array getAnyByCodePromotion(array $listOfCodePromotions, array $options = [])
 * @method bool deleteByCodePromotion(int $code_promotion, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByCodePromotion(int $code_promotion, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByCodePromotion(array $listOfCodePromotions, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByCodePromotion(int $code_promotion, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByCodePromotion(int $code_promotion, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByCodePromotion(array $listOfCodePromotions, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByName(string $name, array $options = [])
 * @method array getAllByName(string $name, array $options = [])
 * @method array getAnyByName(array $listOfNames, array $options = [])
 * @method bool deleteByName(string $name, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByName(string $name, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByName(array $listOfNames, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByName(string $name, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByName(string $name, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByName(array $listOfNames, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByAddress(string $address, array $options = [])
 * @method array getAllByAddress(string $address, array $options = [])
 * @method array getAnyByAddress(array $listOfAddresses, array $options = [])
 * @method bool deleteByAddress(string $address, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByAddress(string $address, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByAddress(array $listOfAddresses, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByAddress(string $address, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByAddress(string $address, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByAddress(array $listOfAddresses, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByCity(string $city, array $options = [])
 * @method array getAllByCity(string $city, array $options = [])
 * @method array getAnyByCity(array $listOfCities, array $options = [])
 * @method bool deleteByCity(string $city, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByCity(string $city, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByCity(array $listOfCities, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByCity(string $city, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByCity(string $city, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByCity(array $listOfCities, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByPostalCode(string $postal_code, array $options = [])
 * @method array getAllByPostalCode(string $postal_code, array $options = [])
 * @method array getAnyByPostalCode(array $listOfPostalCodes, array $options = [])
 * @method bool deleteByPostalCode(string $postal_code, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByPostalCode(string $postal_code, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByPostalCode(array $listOfPostalCodes, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByPostalCode(string $postal_code, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByPostalCode(string $postal_code, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByPostalCode(array $listOfPostalCodes, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByProvince(string $province, array $options = [])
 * @method array getAllByProvince(string $province, array $options = [])
 * @method array getAnyByProvince(array $listOfProvinces, array $options = [])
 * @method bool deleteByProvince(string $province, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByProvince(string $province, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByProvince(array $listOfProvinces, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByProvince(string $province, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByProvince(string $province, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByProvince(array $listOfProvinces, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByTelephone(string $telephone, array $options = [])
 * @method array getAllByTelephone(string $telephone, array $options = [])
 * @method array getAnyByTelephone(array $listOfTelephones, array $options = [])
 * @method bool deleteByTelephone(string $telephone, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByTelephone(string $telephone, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByTelephone(array $listOfTelephones, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByTelephone(string $telephone, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByTelephone(string $telephone, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByTelephone(array $listOfTelephones, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByEmail(string $email, array $options = [])
 * @method array getAllByEmail(string $email, array $options = [])
 * @method array getAnyByEmail(array $listOfEmails, array $options = [])
 * @method bool deleteByEmail(string $email, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByEmail(string $email, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByEmail(array $listOfEmails, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByEmail(string $email, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByEmail(string $email, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByEmail(array $listOfEmails, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByDocument(string $document, array $options = [])
 * @method array getAllByDocument(string $document, array $options = [])
 * @method array getAnyByDocument(array $listOfDocuments, array $options = [])
 * @method bool deleteByDocument(string $document, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByDocument(string $document, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByDocument(array $listOfDocuments, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByDocument(string $document, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByDocument(string $document, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByDocument(array $listOfDocuments, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByAcceptsLegal(int $accepts_legal, array $options = [])
 * @method array getAllByAcceptsLegal(int $accepts_legal, array $options = [])
 * @method array getAnyByAcceptsLegal(array $listOfAcceptsLegals, array $options = [])
 * @method bool deleteByAcceptsLegal(int $accepts_legal, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByAcceptsLegal(int $accepts_legal, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByAcceptsLegal(array $listOfAcceptsLegals, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByAcceptsLegal(int $accepts_legal, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByAcceptsLegal(int $accepts_legal, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByAcceptsLegal(array $listOfAcceptsLegals, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByAcceptsInfo(int $accepts_info, array $options = [])
 * @method array getAllByAcceptsInfo(int $accepts_info, array $options = [])
 * @method array getAnyByAcceptsInfo(array $listOfAcceptsInfos, array $options = [])
 * @method bool deleteByAcceptsInfo(int $accepts_info, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByAcceptsInfo(int $accepts_info, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByAcceptsInfo(array $listOfAcceptsInfos, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByAcceptsInfo(int $accepts_info, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByAcceptsInfo(int $accepts_info, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByAcceptsInfo(array $listOfAcceptsInfos, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByRequiresAddress(int $requires_address, array $options = [])
 * @method array getAllByRequiresAddress(int $requires_address, array $options = [])
 * @method array getAnyByRequiresAddress(array $listOfRequiresAddresses, array $options = [])
 * @method bool deleteByRequiresAddress(int $requires_address, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByRequiresAddress(int $requires_address, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByRequiresAddress(array $listOfRequiresAddresses, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByRequiresAddress(int $requires_address, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByRequiresAddress(int $requires_address, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByRequiresAddress(array $listOfRequiresAddresses, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByIsCompleted(int $is_completed, array $options = [])
 * @method array getAllByIsCompleted(int $is_completed, array $options = [])
 * @method array getAnyByIsCompleted(array $listOfIsCompleteds, array $options = [])
 * @method bool deleteByIsCompleted(int $is_completed, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByIsCompleted(int $is_completed, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByIsCompleted(array $listOfIsCompleteds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByIsCompleted(int $is_completed, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByIsCompleted(int $is_completed, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByIsCompleted(array $listOfIsCompleteds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByIsSent(int $is_sent, array $options = [])
 * @method array getAllByIsSent(int $is_sent, array $options = [])
 * @method array getAnyByIsSent(array $listOfIsSents, array $options = [])
 * @method bool deleteByIsSent(int $is_sent, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByIsSent(int $is_sent, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByIsSent(array $listOfIsSents, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByIsSent(int $is_sent, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByIsSent(int $is_sent, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByIsSent(array $listOfIsSents, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByCreatedByUserId(int $created_by_user_id, array $options = [])
 * @method array getAllByCreatedByUserId(int $created_by_user_id, array $options = [])
 * @method array getAnyByCreatedByUserId(array $listOfCreatedByUserIds, array $options = [])
 * @method bool deleteByCreatedByUserId(int $created_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByCreatedByUserId(int $created_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByCreatedByUserId(array $listOfCreatedByUserIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByCreatedByUserId(int $created_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByCreatedByUserId(int $created_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByCreatedByUserId(array $listOfCreatedByUserIds, array $getOptions = [], array $updateOptions = [])
 * @method array|object getByModifiedByUserId(int $modified_by_user_id, array $options = [])
 * @method array getAllByModifiedByUserId(int $modified_by_user_id, array $options = [])
 * @method array getAnyByModifiedByUserId(array $listOfModifiedByUserIds, array $options = [])
 * @method bool deleteByModifiedByUserId(int $modified_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAllByModifiedByUserId(int $modified_by_user_id, array $getOptions = [], array $deleteOptions = []))
 * @method bool deleteAnyByModifiedByUserId(array $listOfModifiedByUserIds, array $getOptions = [], array $deleteOptions = []))
 * @method array updateByModifiedByUserId(int $modified_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAllByModifiedByUserId(int $modified_by_user_id, array $getOptions = [], array $updateOptions = [])
 * @method array updateAnyByModifiedByUserId(array $listOfModifiedByUserIds, array $getOptions = [], array $updateOptions = [])
 * </MAGIC_METHODS>
 */
class Participant extends AppMySQLModel
{
    /**
     * Model name
     *
     * @var string
     */
    public $name = 'Participant';

    /**
     * Model auto config generation
     *
     * @var array
     */
    protected $autoconfig = true;

    /**
     * All mysql tables require created and modified fields. If this is set to true, the table also requires
     * created_by_user_id and modified_by_user_id and both area populated automatically.
     *
     * @var bool
     */
    protected $user_timestamp_fields = true;

    /**
     * Adds a new record to the table.
     *
     * @param $values array values to be inserted
     * @param array $options
     *  - reset_errors: if set to false, ::$errors will not be reset at the beginning of execution (for nested calls)
     *
     * @return array|bool
     */
    public function addNew($values = [], $options = [])
    {
        if (!empty($values['code'])) {
            $values['code'] = trim($this->currentValue('code', $element, $values));
            $previous_id = $this->field('id', ['where' => [
                'code' => $values['code'],
                'is_completed' => 0,
            ]]);

            if ($previous_id) {
                $this->deleteById($previous_id);
            }
        }

        return parent::addNew($values, $options);
    }

    /**
     * Before validate callback.
     * Executed before validation on updateFields and addNew calls.
     *
     * @param $element array element being updated
     * @param $values array values being updated
     * @param $options array options
     *
     * @return bool
     */
    public function beforeValidate($element, &$values, $options)
    {
        if (!parent::beforeValidate($element, $values, $options)) {
            return false;
        }

        if ($element) {
            // Ummutable
            unset($values['code']);
            unset($values['code_supermarket']);
            unset($values['code_promotion']);
            unset($values['requires_address']);
        } else {
            $values['barcode'] = strtoupper(random_token(false, 10, 10) . time());
            $values['requires_address'] = 0;
            $values['code'] = trim($this->currentValue('code', $element, $values));
            $promotionalCode = new PromotionalCode($values['code']);

            if (!$promotionalCode->isValid()) {
                $this->errorCodesByField['code'] = 'invalid';
                $this->errors[] = $this->errorsByField['code'] = __('Invalid code.', true);
                return false;
            } else {
                $values['code_promotion'] = $promotionalCode->getPromotionCode();
                $values['code_supermarket'] = $promotionalCode->getSupermarket();
            }

            $qr_path = PUBLIC_PATH . 'content/qr/' . $values['barcode'] . '.jpg';

            if (!file_exists(dirname($qr_path))) {
                mkdir(dirname($qr_path), 0755, true);
            }

            if (!file_exists($qr_path)) {
                $options = new chillerlan\QRCode\QROptions([
                    'version'    => 5,
                    'outputType' => chillerlan\QRCode\QRCode::OUTPUT_IMAGE_JPG,
                    'eccLevel'   => chillerlan\QRCode\QRCode::ECC_L,
                ]);
                (new chillerlan\QRCode\QRCode($options))->render(Router::url(['controller' => 'QrValidator', 'action' => 'validate', $values['barcode']]), $qr_path);
            }
        }

        $formFields = [
            'name',
            'address',
            'city',
            'postal_code',
            'province',
            'telephone',
            'email',
            //'document',
            'accepts_legal',
        ];

        if (
            $element
            && $element[$this->name]['is_completed']
        ) {
            // Ummutable
            foreach ($formFields as $field) {
                unset($values[$field]);
            }
        } else {
            if ($this->valueHasChanged($formFields, $element, $values)) {
                $values['is_completed'] = 1;

                foreach ($formFields as $field) {
                    if (
                        !$this->currentValue($field, $element, $values)
                        && ($this->currentValue('requires_address', $element, $values)
                            || !in_array($field, [
                                'address',
                                'city',
                                'postal_code',
                                'province',
                            ]))
                    ) {
                        $this->errorCodesByField[$field] = 'notempty';
                        $this->errors[] = $this->errorsByField[$field] = $this->translateValidationError($field, '%readable% cannot be left empty.');
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function isValidSpanishIdentityDocument($element, &$values, $field)
    {
        $document = $this->currentValue($field, $element, $values);
        $document = trim(strtoupper($document));

        if ($document && strlen($document) < 9) {
            if (@is_nan(substr($document, 0, 1))) {
                $first_char = substr($document, 0, 1);
                $dc = substr($document, -1);
                $document_number = str_pad(substr($document, 1, -1), 7, '0', STR_PAD_LEFT);

                if (!@is_nan($document_number)) {
                    $document = $first_char . $document_number . $dc;
                }
            } else {
                $dc = substr($document, -1);
                $document_number = str_pad(substr($document, 0, -1), 8, '0', STR_PAD_LEFT);

                if (!@is_nan($document_number)) {
                    $document = $document_number . $dc;
                }
            }
        }

        unset($first_char);
        unset($dc);
        unset($document_number);

        if (!ltrim($document, '0')) {
            $document = '';
        }

        $num = [];

        for ($i = 0; $i < 9; $i++) {
            $num[$i] = substr($document, $i, 1);
        }

        // Check for valid format
        if (!preg_match('/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/i', $document)) {
            return false;
        }

        // Standar NIF check
        if (preg_match('/(^[0-9]{8}[A-Z]{1}$)/i', $document)) {
            return ($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($document, 0, 8) % 23, 1));
        }

        $sum = $num[2] + $num[4] + $num[6];

        for ($i = 1; $i < 8; $i += 2) {
            $sum += @substr((2 * $num[$i]), 0, 1) + substr((2 * $num[$i]), 1, 1);
        }

        $n = 10 - @substr($sum, strlen($sum) - 1, 1);

        // Special NIF check
        if (preg_match('/^[KLM]{1}/i', $document)) {
            return ($num[8] == chr(64 + $n));
        }

        // CIF check
        if (preg_match('/^[ABCDEFGHJNPQRSUVW]{1}/i', $document)) {
            return false; // No cif
            //return ($num[8] == chr(64 + $n) || $num[8] == substr($n, strlen($n) - 1, 1));
        }

        // NIE check
        if (preg_match('/^[T]{1}/i', $document)) {
            return ($num[8] == preg_match('/^[T]{1}[A-Z0-9]{8}$/i', $document));
        }

        // XYZ
        if (preg_match('/^[XYZ]{1}/i', $document)) {
            return ($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr(str_replace([
                'X',
                'Y',
                'Z',
            ], [
                '0',
                '1',
                '2',
            ], $document), 0, 8) % 23, 1));
        }

        // If not found, error
        return false;
    }

    protected function afterSave($created, $element, $values, $originalElement, $options)
    {
        if (
            $this->valueHasChanged('is_completed', $originalElement, $element[$this->name])
            && $element[$this->name]['is_completed']
        ) {
            /** @var Store $Store **/
            $Store = MySQLModel::getRecycledInstance('Store', [], $this);
            $group = $Store->field('group', ['where' => ['code' => $element[$this->name]['code_supermarket']]]);

            if ($group) {
                /** @var Moment $Moment **/
                $Moment = MySQLModel::getRecycledInstance('Moment', [], $this);
                $Moment->query('UPDATE ' . $Moment->full_table_name . ' SET `is_used` = 1, `participant_id` = \'' . sql_escape($element[$this->name]['id']) . '\' WHERE `is_used` = 0 AND `group` = \'' . sql_escape($group) . '\' AND `date` < NOW() ORDER BY `date` ASC LIMIT 1;');
                $moment_id = $Moment->field('id', ['where' => ['participant_id' => $element[$this->name]['id']]]);

                if ($moment_id) {
                    $this->updateFields($element[$this->name]['id'], ['is_winner' => 1]);
                }
            }
        }

        if (
            ($this->valueHasChanged('email', $originalElement, $element[$this->name])
                || $this->valueHasChanged('is_winner', $originalElement, $element[$this->name]))
            && $element[$this->name]['is_winner']
        ) {
            $this->sendEmailRef(1, $element[$this->name]['id']);
        }

        parent::afterSave($created, $element, $values, $originalElement, $options);
    }

    public function sendEmailRef($reference, $id, $fake_email = null)
    {
        $element = $this->getById($id);

        if ($fake_email) {
            $element[$this->name]['alternative_email'] = $fake_email;
        }

        $subject = [
            1 => 'Aquí tienes tu entrada para dos personas para el Gran Showcooking de Navidad',
            2 => 'Comienza la cuenta atrás. Queda una semana para el Gran Showcooking de Navidad con Pepe Rodríguez y Nacho Manzano',
            3 => 'Aquí tienes las recetas del Gran Showcooking de Navidad con Pepe Rodríguez y Nacho Manzano',
            4 => 'No queda nada. Mañana tienes una cita con Pepe Rodríguez y Nacho Manzano en el Gran Showcooking de Navidad',
            5 => 'Disfruta de nuevo del Gran Showcooking de Navidad con Pepe Rodríguez y Nacho Manzano',
        ][$reference];
        $template = [
            1 => 'winner',
            2 => 'link-week',
            3 => 'link-recipes',
            4 => 'link-tomorrow',
            5 => 'link-replay',
        ][$reference];
        $email = [
            1 => $element[$this->name]['alternative_email'] ? $element[$this->name]['alternative_email'] : $element[$this->name]['email'],
            2 => $element[$this->name]['alternative_email'] ? $element[$this->name]['alternative_email'] : $element[$this->name]['email'],
            3 => $element[$this->name]['alternative_email'] ? $element[$this->name]['alternative_email'] : $element[$this->name]['email'],
            4 => $element[$this->name]['alternative_email'] ? $element[$this->name]['alternative_email'] : $element[$this->name]['email'],
            5 => $element[$this->name]['alternative_email'] ? $element[$this->name]['alternative_email'] : $element[$this->name]['email'],
        ][$reference];

        $subject = str_replace(
            [
                '[NOMBRE]',
            ],
            [
                $element[$this->name]['name'],
            ],
            $subject
        );

        return Email::sendTemplate($subject, $email, [], $template, [
            'participant' => $element,
            'subject' => $subject,
            'root' => ROOT_URL,
        ], null, [], 'email-participant');
    }
}
