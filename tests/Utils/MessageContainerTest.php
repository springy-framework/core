<?php
/**
 * Test case for Springy\Utils\MessageContainer class.
 *
 * @copyright 2015 Fernando Val
 * @author    Allan Marques <allan.marques@ymail.com>
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */
use PHPUnit\Framework\TestCase;
use Springy\Utils\MessageContainer;

class MessageContainerTest extends TestCase
{
    protected const LI_TPL = '<li>:msg</li>';

    protected $msgContainer;

    protected function setUp(): void
    {
        $this->msgContainer = new MessageContainer();
    }

    public function testMessageGetsFormated()
    {
        $this->msgContainer->setMessages(['errors' => 'Erro!']);
        $msg = $this->msgContainer->get('errors', self::LI_TPL);

        $this->assertEquals(['<li>Erro!</li>'], $msg);
    }

    public function testMultipleMessagesGetsFormated()
    {
        $this->msgContainer->add('errors', 'Error1');
        $this->msgContainer->add('errors', 'Error2');
        $this->msgContainer->add('errors', 'Error3');

        $msg = $this->msgContainer->get('errors', self::LI_TPL);

        $this->assertEquals(['<li>Error1</li>', '<li>Error2</li>', '<li>Error3</li>'], $msg);
    }

    public function testGetJustFirstMessageOfAType()
    {
        $this->msgContainer->add('errors', 'Error1');
        $this->msgContainer->add('errors', 'Error2');
        $this->msgContainer->add('errors', 'Error3');

        $msg = $this->msgContainer->first('errors', self::LI_TPL);

        $this->assertEquals('<li>Error1</li>', $msg);
    }

    public function testGetsAllMessages()
    {
        $this->msgContainer->add('errors', 'Error');
        $this->msgContainer->add('success', 'Success');
        $this->msgContainer->add('warning', 'Warning');

        $msg = $this->msgContainer->all(self::LI_TPL);

        $this->assertEquals(
            [
                '<li>Error</li>',
                '<li>Success</li>',
                '<li>Warning</li>',
            ],
            $msg
        );
    }
}
