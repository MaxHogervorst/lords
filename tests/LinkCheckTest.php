<?php


class LinkCheckTest extends TestCase
{
    public function testHome()
    {
        $this->visit('/')
            ->dontSee('Whoops')
            ->seePageIs('auth/login');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->visit('/')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/');
    }

    public function testMembers()
    {
        $this->visit('/member')
            ->dontSee('Whoops')
            ->seePageIs('auth/login');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->visit('/member')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/member');
    }

    public function testGroups()
    {
        $this->visit('/group')
            ->dontSee('Whoops')
            ->seePageIs('auth/login');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->visit('/group')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/group');
    }

    public function testProducts()
    {
        $this->visit('/product')
            ->dontSee('Whoops')
            ->seePageIs('auth/login');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->visit('/product')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/product');
    }

    public function testFiscus()
    {
        $this->visit('/fiscus')
            ->dontSee('Whoops')
            ->seePageIs('auth/login');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->visit('/fiscus')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/fiscus');
        Sentinel::logout();

        $user = Sentinel::findById(1);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(1))
            ->withSession([])
            ->visit('/fiscus')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/');
    }

    public function testInvoice()
    {
        $this->visit('/invoice')
            ->dontSee('Whoops')
            ->seePageIs('auth/login');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->visit('/invoice')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/invoice');
        Sentinel::logout();

        $user = Sentinel::findById(1);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(1))
            ->withSession([])
            ->visit('/invoice')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/');
    }

    public function testSepa()
    {
        $this->visit('/sepa')
            ->dontSee('Whoops')
            ->seePageIs('auth/login');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->visit('/sepa')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/sepa');
        Sentinel::logout();

        $user = Sentinel::findById(1);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(1))
            ->withSession([])
            ->visit('/sepa')
            ->dontSee('Whoops')
            ->dontSee('auth/login')
            ->seePageIs('/');
    }
}
