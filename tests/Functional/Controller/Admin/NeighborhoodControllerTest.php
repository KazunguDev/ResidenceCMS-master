<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Entity\Neighborhood;
use App\Tests\Helper\WebTestHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class NeighborhoodControllerTest extends WebTestCase
{
    use WebTestHelper;

    private const NAME = 'Test';
    private const SLUG = 'test';
    private const EDITED_NAME = 'Edited';

    /**
     * This test changes the database contents by creating a new Neighborhood.
     */
    public function testAdminNewNeighborhood(): void
    {
        $client = $this->authAsAdmin($this);
        $crawler = $client->request('GET', '/en/admin/locations/neighborhood/new');

        $form = $crawler->selectButton('Create neighborhood')->form([
            'neighborhood[name]' => self::NAME,
            'neighborhood[slug]' => self::SLUG,
        ]);
        $client->submit($form);

        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $neighborhood = $this->getRepository($client, Neighborhood::class)
            ->findOneBy([
                'slug' => self::SLUG,
            ]);

        $this->assertNotNull($neighborhood);
        $this->assertSame(self::NAME, $neighborhood->getName());
        $this->assertSame(self::SLUG, $neighborhood->getSlug());
    }

    /**
     * This test changes the database contents by editing an Neighborhood.
     */
    public function testAdminEditNeighborhood(): void
    {
        $client = $this->authAsAdmin($this);

        $neighborhood = $this->getRepository($client, Neighborhood::class)
            ->findOneBy([
                'slug' => self::SLUG,
            ])->getId();

        $crawler = $client->request('GET', '/en/admin/locations/neighborhood/'.$neighborhood.'/edit');

        $form = $crawler->selectButton('Save changes')->form([
            'neighborhood[name]' => self::EDITED_NAME,
        ]);

        $client->submit($form);
        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $editedNeighborhood = $this->getRepository($client, Neighborhood::class)
            ->findOneBy([
                'id' => $neighborhood,
            ]);

        $this->assertSame(self::EDITED_NAME, $editedNeighborhood->getName());
    }

    /**
     * This test changes the database contents by deleting a test Neighborhood.
     */
    public function testAdminDeleteNeighborhood(): void
    {
        $client = $this->authAsAdmin($this);

        $neighborhood = $this->getRepository($client, Neighborhood::class)
            ->findOneBy([
                'slug' => self::SLUG,
            ])->getId();

        $crawler = $client->request('GET', '/en/admin/locations/neighborhood');
        $client->submit($crawler->filter('#delete-neighborhood-'.$neighborhood)->form());
        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $this->assertNull($this->getRepository($client, Neighborhood::class)->findOneBy([
                'slug' => self::SLUG,
            ]));
    }
}
