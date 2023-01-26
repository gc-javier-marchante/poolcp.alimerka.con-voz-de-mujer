<?php

use Alimerka\PromotionalCode;

/**
 * Class PagesController.
 *
 * Handles static pages related HTTP request.
 */
class PagesController extends AppController
{
    /**
     * Controller name
     */
    public $name = 'Pages';

    /**
     * Set the layout to use
     *
     * @var string
     */
    protected $layout = 'default';

    /**
     * @see Controller::$actionAccessLevel
     */
    protected $actionAccessLevel = [
        '*' => -1,
    ];

    public function index()
    {
        $website_is_closed = time() >= strtotime(date('2022-11-24'));
        $this->set('website_is_closed', $website_is_closed);

        if (!$website_is_closed) {
            // Posted non-empty form data
            $participantData = post_var('participant[Participant]');
            $participantData = ($participantData ? array_filter($participantData) : null);

            if ($participantData) {
                /** @var Participant $Participant **/
                $Participant = MySQLModel::getInstance('Participant');
                $participant = $Participant->addNew($participantData);

                if ($participant) {
                    Session::set('participant_id', $participant['Participant']['id']);
                    $this->redirect(['action' => 'thanks']);
                } elseif (
                    !empty($Participant->errorCodesByField['code'])
                    && $Participant->errorCodesByField['code'] == 'unique'
                ) {
                    $this->set('used', true);
                } elseif (
                    !empty($Participant->errorCodesByField['code'])
                    && $Participant->errorCodesByField['code'] == 'invalid'
                ) {
                    $this->set('invalid', true);
                } else {
                    $this->set('generic_error', true);
                }
            }
        }

        $this->set('menu_active', true);
    }

    public function thanks()
    {
        $participant_id = Session::get('participant_id');

        if (!$participant_id) {
            $this->redirect(['action' => 'index']);
        }

        /** @var Participant $Participant **/
        $Participant = MySQLModel::getInstance('Participant');
        $participant = $Participant->getById($participant_id, [
            'recursive' => 0,
        ]);

        if (!$participant) {
            $this->redirect(['action' => 'index']);
        }

        if ($participant['Participant']['is_completed']) {
            Session::destroy();
            $this->set('completed', true);
            $this->set('winner', !!$participant['Participant']['is_winner']);
        } elseif (post_var('participant[Participant]')) {
            $participantData = post_var('participant[Participant]');

            if (empty(!$participantData['accepts_legal'])) {
                $savedParticipant = $Participant->updateFields($participant_id, $participantData);
            } else {
                $savedParticipant = false;
                $Participant->errors[] = __('You must accept the terms and conditions.', true);
            }

            if ($savedParticipant) {
                $this->redirect(['action' => 'thanks']);
            } else {
                $error_message = __('Could not save.', true);

                foreach ($Participant->errors as $error) {
                    $error_message .= ' ' . $error;
                }

                Session::set('flash[message]', $error_message);
            }
        }
    }

    public function termsConditions()
    {
        $this->set('seo_title', 'Términos y condiciones | ' . GestyMVC::config('website_name'));
    }

    public function privacyPolicy()
    {
        $this->set('seo_title', 'Política de privacidad | ' . GestyMVC::config('website_name'));
    }

    public function cookiePolicy()
    {
        $this->set('seo_title', 'Política de cookies | ' . GestyMVC::config('website_name'));
    }

    public function legalNotice()
    {
        $this->set('seo_title', 'Aviso legal | ' . GestyMVC::config('website_name'));
    }

    public function establishments()
    {
        $this->set('seo_title', 'Establecimientos | ' . GestyMVC::config('website_name'));
    }

    public function winners()
    {
        $this->set('seo_title', 'Ganadores | ' . GestyMVC::config('website_name'));

        /** @var AppMySQLModel $WinnerPageSection **/
        $WinnerPageSection = MySQLModel::getInstance('WinnerPageSection');
        $this->set('winnerPageSections', $WinnerPageSection->getAll());
    }

    public function faq()
    {
        $this->set('seo_title', 'Preguntas frecuentes | ' . GestyMVC::config('website_name'));
    }

    public function hidePopup()
    {
        $this->view = false;
        $this->layout = false;

        Session::set('popup_hidden', true);
    }

    public function email($hash)
    {
        $data = @hex_decode($hash);
        $data = @json_decode($data);

        if (!$data || sizeof($data) != 3) {
            $this->notFound(false);
        }

        $participant_id = $data[0];
        $url = $data[1];
        $token = $data[2];

        if ($token != md5($url . $participant_id . 'email')) {
            $this->notFound(false);
        }

        /** @var ParticipantEmailClick $ParticipantEmailClick **/
        $ParticipantEmailClick = MySQLModel::getRecycledInstance('ParticipantEmailClick', [], $this);
        $ParticipantEmailClick->addNew([
            'participant_id' => $participant_id,
            'url' => $url,
        ]);

        $this->redirect($url);
    }
}
