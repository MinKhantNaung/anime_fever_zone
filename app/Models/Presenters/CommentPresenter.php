<?php

namespace App\Models\Presenters;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\HtmlString;

final class CommentPresenter
{
    public Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function markdownBody(): HtmlString
    {
        // Use a markdown parser that sanitizes HTML
        $html = app('markdown')->convertToHtml($this->comment->body);

        // Sanitize HTML to allow only safe tags
        $html = strip_tags($html, '<p><strong><em><code><pre><a><ul><ol><li>');

        // Or use a proper HTML sanitizer like HTMLPurifier
        return new HtmlString($html);
    }

    public function relativeCreatedAt(): mixed
    {
        return $this->comment->created_at->diffForHumans();
    }

    public function replaceUserMentions($text): array|string
    {
        preg_match_all('/@([A-Za-z0-9_]+)/', $text, $matches);
        $usernames = $matches[1];
        $replacements = [];

        foreach ($usernames as $username) {
            $user = User::where('name', $username)->first();
            if ($user) {
                $userRoutePrefix = config('commentify.users_route_prefix', 'users');
                $replacements['@' . $username] = '<a href="/' . $userRoutePrefix . '/' . $username . '">@' . $username .
                    '</a>';
            } else {
                $replacements['@' . $username] = '@' . $username;
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
