<?php
namespace CakeOven\CakeCsv\View;

use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\View\View;

class CsvView extends View
{

    /**
     * Constructor
     *
     * @param \Cake\Network\Request    $request      Request instance.
     * @param \Cake\Network\Response   $response     Response instance.
     * @param \Cake\Event\EventManager $eventManager EventManager instance.
     * @param array                    $viewOptions  An array of view options
     */
    public function __construct(Request $request = null, Response $response = null, EventManager $eventManager = null, array $viewOptions = [])
    {
        parent::__construct($request, $response, $eventManager, $viewOptions);
        if ($response && $response instanceof Response) {
            $response->type('csv');
        }
    }

    public function render($view = null, $layout = null)
    {
        $this->response->download($filename);
        $this->response->body($csv);
        return parent::render($view, $layout);
    }
}

