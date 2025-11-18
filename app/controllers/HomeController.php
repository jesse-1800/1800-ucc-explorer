<?php

use App\Models\LanguageModel;

/**
 * Default controller
 * Class HomeController
 */
class HomeController
{
    /**
     * @return View
     */
    public function index()
    {
        return View::render(SYSTEM_MODE.TEMPLATE_TYPE);
    }

    /**
     * We're testing the LLM integration here
     * @return void
     */
    public function test()
    {
        $llm = new LanguageModel();
        /*$result = $llm->completions([
            [
                'role' => 'system',
                'content' => 'Tell me a joke about programming in less than 20 words.'
            ],
        ]);
        dump($result);*/
    }
}
