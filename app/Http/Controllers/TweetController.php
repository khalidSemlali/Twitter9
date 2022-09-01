<?php

namespace App\Http\Controllers; 

use App\Models\User;
use Inertia\Inertia;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class TweetController extends Controller
{
    public function index()
    {
        $tweets = Tweet::with([
            'user' => fn ($query) => $query->withCount([
                'followers as is_followed' 
                => fn ($query) => $query->where('follower_id', auth()->user()->id)
            ])
            ->withCasts(['is_followed' => 'boolean'])
        ])
        ->orderBy('created_at', 'DESC')
        ->get();

        return Inertia::render('Tweets/Index', [
            'tweets' => $tweets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => ['required', 'max:280'],
            'user_id' => ['exists:users,id']
        ]);

        Tweet::create([
            'content' => $request->input('content'),
            'user_id' => auth()->user()->id
        ]);

        return Redirect::route('tweets.index');
    }

    public function follows(User $user)
    {
        auth()->user()->followings()->attach($user->id);

        return Redirect::route('tweets.index');
    }

    public function unfollows(User $user)
    {
        auth()->user()->followings()->detach($user->id);

        return Redirect::route('tweets.index');
    }
}
