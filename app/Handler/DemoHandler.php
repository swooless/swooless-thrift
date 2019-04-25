<?php declare(strict_types=1);

namespace App\Handler;

use Swooless\Protocol\Demo\NotFoundException;
use Swooless\Protocol\Demo\NotUniqueException;
use Swooless\Protocol\Demo\ServerIf;
use Swooless\Protocol\Demo\Type;
use Swooless\Protocol\Demo\User;

class DemoHandler implements ServerIf
{
    /**
     * @return string
     */
    public function version()
    {
        return "1.0.0";
    }

    /**
     * @param User $user
     * @return bool
     */
    public function add(User $user)
    {
        return true;
    }

    /**
     * @param string $name
     * @return User
     * @throws NotFoundException
     * @throws NotUniqueException
     */
    public function get($name)
    {
        if ('found' == $name) {
            throw new NotFoundException();
        }

        if ('unique' == $name) {
            throw new NotUniqueException();
        }

        return new User([
            'name' => $name,
            'age' => 18,
            'role' => Type::A
        ]);
    }
}