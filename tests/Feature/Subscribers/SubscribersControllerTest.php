<?php

declare(strict_types=1);

namespace Tests\Feature\Subscribers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Cornatul\Marketing\Base\Facades\MarketingPortal;
use Cornatul\Marketing\Base\Models\Subscriber;
use Cornatul\Marketing\Base\Models\Tag;
use Tests\TestCase;

class SubscribersControllerTest extends TestCase
{
    use RefreshDatabase,
        WithFaker;

    /** @test */
    public function new_subscribers_can_be_created_by_authenticated_users()
    {
        // given
        $subscriberStoreData = [
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ];

        // when
        $response = $this->post(route('marketing.subscribers.store'), $subscriberStoreData);

        // then
        $response->assertRedirect();

        $this->assertDatabaseHas('sendportal_subscribers', [
            'workspace_id' => MarketingPortal::currentWorkspaceId(),
            'email' => $subscriberStoreData['email']
        ]);
    }

    /** @test */
    public function the_edit_view_is_accessible_by_authenticated_users()
    {
        // given
        $subscriber = Subscriber::factory()->create(['workspace_id' => MarketingPortal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('marketing.subscribers.edit', $subscriber->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function a_subscriber_is_updateable_by_an_authenticated_user()
    {
        // given
        $subscriber = Subscriber::factory()->create(['workspace_id' => MarketingPortal::currentWorkspaceId()]);

        $subscriberUpdateData = [
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName
        ];

        // when
        $response = $this
            ->put(route('marketing.subscribers.update', $subscriber->id), $subscriberUpdateData);

        // then
        $response->assertRedirect();

        $this->assertDatabaseHas('sendportal_subscribers', [
            'id' => $subscriber->id,
            'email' => $subscriberUpdateData['email'],
            'first_name' => $subscriberUpdateData['first_name'],
            'last_name' => $subscriberUpdateData['last_name'],
        ]);
    }

    /** @test */
    public function the_show_view_is_accessible_by_an_authenticated_user()
    {
        // given
        $subscriber = Subscriber::factory()->create(['workspace_id' => MarketingPortal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('marketing.subscribers.show', $subscriber->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function the_subscribers_index_lists_subscribers()
    {
        // given
        $subscriber = Subscriber::factory()->count(5)->create([
            'workspace_id' => MarketingPortal::currentWorkspaceId(),
        ]);

        // when
        $response = $this->get(route('marketing.subscribers.index'));

        // then
        $subscriber->each(static function (Subscriber $subscriber) use ($response) {
            $response->assertSee($subscriber->email);
            $response->assertSee("{$subscriber->first_name} {$subscriber->last_name}");
        });
    }

    /** @test */
    public function the_subscribers_index_can_be_filtered_by_tags()
    {
        // given
        $firstTag = Tag::factory()->create([
            'workspace_id' => MarketingPortal::currentWorkspaceId(),
        ]);

        $secondTag = Tag::factory()->create([
            'workspace_id' => MarketingPortal::currentWorkspaceId(),
        ]);

        $thirdTag = Tag::factory()->create([
            'workspace_id' => MarketingPortal::currentWorkspaceId(),
        ]);

        $firstTagSubscriber = Subscriber::factory()->create([
            'workspace_id' => MarketingPortal::currentWorkspaceId(),
        ]);

        $secondTagSubscriber = Subscriber::factory()->create([
            'workspace_id' => MarketingPortal::currentWorkspaceId(),
        ]);

        $thirdTagSubscriber = Subscriber::factory()->create([
            'workspace_id' => MarketingPortal::currentWorkspaceId(),
        ]);

        $firstTag->subscribers()->attach($firstTagSubscriber->id);
        $secondTag->subscribers()->attach($secondTagSubscriber->id);
        $thirdTag->subscribers()->attach($thirdTagSubscriber->id);

        // when
        $response = $this->get(route('marketing.subscribers.index', [
            'tags' => [$firstTag->id, $secondTag->id]
        ]));

        // then
        $response->assertSee($firstTagSubscriber->email);
        $response->assertSee("{$firstTagSubscriber->first_name} {$firstTagSubscriber->last_name}");
        $response->assertSee($secondTagSubscriber->email);
        $response->assertSee("{$secondTagSubscriber->first_name} {$secondTagSubscriber->last_name}");
        $response->assertDontSee($thirdTagSubscriber->email);
        $response->assertDontSee("{$thirdTagSubscriber->first_name} {$thirdTagSubscriber->last_name}");
    }
}
