<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Cornatul\Marketing\Base\Facades\MarketingPortal;
use Cornatul\Marketing\Base\Models\Campaign;
use Tests\TestCase;

class CampaignReportsControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_sent_campaign_report_is_accessible_by_authenticated_users()
    {
        // given
        $campaign = $this->getCampaign();

        // when
        $response = $this->get(route('marketing.campaigns.reports.index', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function sent_campaign_recipients_are_accessible_by_authenticated_users()
    {
        // given
        $campaign = $this->getCampaign();

        // when
        $response = $this->get(route('marketing.campaigns.reports.recipients', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function sent_campaign_opens_are_accessible_by_authenticated_users()
    {
        // given
        $campaign = $this->getCampaign();

        // when
        $response = $this->get(route('marketing.campaigns.reports.opens', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function sent_campaign_clicks_are_accessible_by_authenticated_users()
    {
        // given
        $campaign = $this->getCampaign();

        // when
        $response = $this->get(route('marketing.campaigns.reports.clicks', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function sent_campaign_bounces_are_accessible_by_authenticated_users()
    {
        // given
        $campaign = $this->getCampaign();

        // when
        $response = $this->get(route('marketing.campaigns.reports.bounces', $campaign->id));

        // then
        $response->assertOk();
    }

    /** @test */
    public function sent_campaign_unsubscribes_are_accessible_by_authenticated_users()
    {
        // given
        $campaign = $this->getCampaign();

        // when
        $response = $this->get(route('marketing.campaigns.reports.unsubscribes', $campaign->id));

        // then
        $response->assertOk();
    }

    private function getCampaign(): Campaign
    {
        return Campaign::factory()
            ->sent()
            ->create(['workspace_id' => MarketingPortal::currentWorkspaceId()]);
    }
}
