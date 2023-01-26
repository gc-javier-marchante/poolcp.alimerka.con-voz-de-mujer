<?php
class QrValidatorController extends AppController
{
    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => -1,
    ];

    public $name = 'QrValidator';

    public function validate()
    {
        $this->redirect('/');
    }

    public function validateJson($barcode = null)
    {
        $this->layout = 'json';
        $this->view = false;

        $this->resultForLayout['response'] = [
            'succeeded' => false,
            'is_old' => false,
            'is_checked_in' => false,
            'checked_in_at' => null,
            'name' => null,
        ];

        /** @var Participant $Participant **/
        $Participant = MySQLModel::getInstance('Participant');
        $participant = $Participant->get(['where' => [
            'is_winner' => 1,
            'barcode' => $barcode,
        ]]);

        if (!$participant) {
            return;
        }

        $this->resultForLayout['response']['succeeded'] = true;
        $this->resultForLayout['response']['name'] = $participant['Participant']['name'];

        if (!$participant['Participant']['is_checked_in']) {
            $Participant->updateFields($participant['Participant']['id'], [
                'is_checked_in' => 1,
                'checked_in_at' => now(),
            ]);

            $this->resultForLayout['response']['is_checked_in'] = true;
            $this->resultForLayout['response']['checked_in_at'] = now();
        } else {
            $this->resultForLayout['response']['is_old'] = true;
            $this->resultForLayout['response']['is_checked_in'] = !!$participant['Participant']['is_checked_in'];
            $this->resultForLayout['response']['checked_in_at'] = $participant['Participant']['checked_in_at'];
        }
    }

    public function confirm($barcode = null)
    {
        /** @var Participant $Participant **/
        $Participant = MySQLModel::getInstance('Participant');
        $participant = $Participant->get(['where' => [
            'is_winner' => 1,
            'barcode' => $barcode,
        ]]);

        if (!$participant) {
            $this->redirect('/');
        }

        if (!$participant['Participant']['is_confirmed']) {
            $Participant->updateFields($participant['Participant']['id'], ['is_confirmed' => 1, 'confirmed_at' => now()]);
        }

        $this->layout = 'default';
        $this->view = 'pages/thanks';
        $this->set('confirmed', true);
    }
}
