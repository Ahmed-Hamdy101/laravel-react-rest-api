<?php

namespace App\Console\Commands;

use App\Models\User ;
use Illuminate\Console\Command;
use Psy\Readline\Hoa\Console;
use Psy\Readline\Hoa\ConsoleOutput;

class TokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:generate {id}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // get id of user
        $id = $this->argument('id');

        // get user by id
        $user = User::find($id);

        if(!$user){
            $this->error("User with id {$id} not found.");
            return 1; // Return a non-zero exit code to indicate an error
        }
        // set user
        // \Auth::setUser($user);

        // generate token
        $console = new ConsoleOutput();
        $token = $user->createToken('admin')->accessToken;   // Passport

        $this->info($token);   // better than ConsoleOutput
        // or: $this->line($token);    }
    }
}