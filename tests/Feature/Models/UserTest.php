<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UserTest extends TestCase
{
    use RefreshDatabase;

    /** Store */
    public function test_it_can_be_created(): void
    {
        $user = User::create([
            'name' => 'Naruto Uzumaki',
            'email' => 'naruto@animefeverzone.test',
            'password' => 'secret-password',
        ]);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Naruto Uzumaki',
            'email' => 'naruto@animefeverzone.test',
        ]);
    }

    public function test_it_defaults_to_the_user_role(): void
    {
        $user = User::factory()->create();

        $this->assertSame('user', $user->fresh()->role);
    }

    public function test_it_only_mass_assigns_name_email_and_password(): void
    {
        $this->assertSame(['name', 'email', 'password'], (new User)->getFillable());
    }

    public function test_it_guards_the_role_against_mass_assignment(): void
    {
        $user = User::create([
            'name' => 'Sasuke Uchiha',
            'email' => 'sasuke@animefeverzone.test',
            'password' => 'secret-password',
            'role' => 'blogger',
        ]);

        $this->assertSame('user', $user->fresh()->role);
    }

    public function test_it_hashes_the_password_on_save(): void
    {
        $user = User::factory()->create([
            'password' => 'secret-password',
        ]);

        $this->assertNotSame('secret-password', $user->password);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }

    public function test_it_hides_the_password_and_remember_token_when_serialized(): void
    {
        $serialized = User::factory()->create()->toArray();

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
        $this->assertArrayHasKey('email', $serialized);
    }

    public function test_it_casts_the_email_verified_at_to_a_datetime(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
    }

    public function test_it_can_be_created_without_a_verified_email(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
        $this->assertFalse($user->hasVerifiedEmail());
    }

    /** Relationships */
    public function test_it_has_one_media(): void
    {
        $user = User::factory()->create();

        $media = $user->media()->create([
            'url' => 'media/avatar.webp',
            'mime' => 'image',
        ]);

        $this->assertInstanceOf(Media::class, $user->fresh()->media);
        $this->assertSame($media->id, $user->fresh()->media->id);
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'mediable_type' => User::class,
            'mediable_id' => $user->id,
            'url' => 'media/avatar.webp',
        ]);
    }

    public function test_its_media_is_null_when_none_has_been_uploaded(): void
    {
        $this->assertNull(User::factory()->create()->media);
    }

    public function test_it_only_returns_its_own_media(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $media = Media::factory()->for($user, 'mediable')->create();
        Media::factory()->for($otherUser, 'mediable')->create();

        $this->assertSame($media->id, $user->media->id);
        $this->assertSame(2, Media::count());
    }

    public function test_it_has_many_likes(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create();

        CommentLike::factory()->count(3)->create([
            'user_id' => $user->id,
            'comment_id' => $comment->id,
        ]);

        $this->assertCount(3, $user->likes);
        $this->assertInstanceOf(CommentLike::class, $user->likes->first());
    }

    public function test_it_does_not_report_likes_belonging_to_other_users(): void
    {
        $user = User::factory()->create();

        CommentLike::factory()->create(['user_id' => $user->id]);
        CommentLike::factory()->count(2)->create();

        $this->assertCount(1, $user->likes);
    }

    /** Traits */
    public function test_it_exposes_a_default_avatar(): void
    {
        $this->assertSame(asset('avatar.webp'), User::factory()->create()->avatar());
    }
}
