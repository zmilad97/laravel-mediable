<?php

namespace Plank\Mediable\Tests\Integration;

use Plank\Mediable\Media;
use Plank\Mediable\MediableCollection;
use Plank\Mediable\Tests\Mocks\SampleMediable;
use Plank\Mediable\Tests\TestCase;

class MediableCollectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->useDatabase();
    }

    public function test_it_can_lazy_eager_load_media()
    {
        $mediable = factory(SampleMediable::class)->create();
        $media = factory(Media::class)->create();
        $mediable->attachMedia($media, 'foo');

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMedia());
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertFalse($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertFalse($collection[0]->media[0]->relationLoaded('variants'));
    }

    public function test_it_can_lazy_eager_load_media_by_tag()
    {
        $mediable = factory(SampleMediable::class)->create();
        $media1 = factory(Media::class)->create(['id' => 1]);
        $media2 = factory(Media::class)->create(['id' => 2]);
        $mediable->attachMedia($media1, 'foo');
        $mediable->attachMedia($media2, 'bar');

        $collection = new MediableCollection([SampleMediable::first()]);
        $return = $collection->loadMedia(['bar']);

        $this->assertSame($collection, $return);
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertEquals([2], $collection[0]->media->pluck('id')->toArray());
        $this->assertFalse($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertFalse($collection[0]->media[0]->relationLoaded('variants'));
    }

    public function test_it_can_lazy_eager_load_media_by_tag_match_all()
    {
        $mediable = factory(SampleMediable::class)->create();
        $media1 = factory(Media::class)->create(['id' => 1]);
        $media2 = factory(Media::class)->create(['id' => 2]);
        $mediable->attachMedia($media1, 'foo');
        $mediable->attachMedia($media2, ['foo', 'bar', 'baz']);

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMedia(['foo', 'bar'], true));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertEquals([2, 2], $collection[0]->media->pluck('id')->toArray());
        $this->assertFalse($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertFalse($collection[0]->media[0]->relationLoaded('variants'));

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMediaMatchAll(['foo', 'bar']));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertEquals([2, 2], $collection[0]->media->pluck('id')->toArray());
        $this->assertFalse($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertFalse($collection[0]->media[0]->relationLoaded('variants'));
    }

    public function test_it_can_lazy_eager_load_media_with_variants()
    {
        $mediable = factory(SampleMediable::class)->create();
        $media = factory(Media::class)->create();
        $mediable->attachMedia($media, 'foo');

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMedia([], false, true));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('variants'));

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMediaWithVariants([]));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('variants'));
    }

    public function test_it_can_lazy_eager_load_media_with_variants_by_tag()
    {
        $mediable = factory(SampleMediable::class)->create();
        $media1 = factory(Media::class)->create(['id' => 1]);
        $media2 = factory(Media::class)->create(['id' => 2]);
        $mediable->attachMedia($media1, 'foo');
        $mediable->attachMedia($media2, 'bar');

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMedia(['bar'], false, true));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertEquals([2], $collection[0]->media->pluck('id')->toArray());
        $this->assertTrue($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('variants'));

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMediaWithVariants(['bar']));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('variants'));
    }

    public function test_it_can_lazy_eager_load_media_with_relations_by_tag_match_all()
    {
        $mediable = factory(SampleMediable::class)->create();
        $media1 = factory(Media::class)->create(['id' => 1]);
        $media2 = factory(Media::class)->create(['id' => 2]);
        $mediable->attachMedia($media1, 'foo');
        $mediable->attachMedia($media2, ['foo', 'bar', 'baz']);

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMedia(['foo', 'bar'], true, true));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertEquals([2, 2], $collection[0]->media->pluck('id')->toArray());
        $this->assertTrue($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('variants'));

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMediaWithVariants(['foo', 'bar'], true));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertEquals([2, 2], $collection[0]->media->pluck('id')->toArray());
        $this->assertTrue($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('variants'));

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMediaMatchAll(['foo', 'bar'], true));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertEquals([2, 2], $collection[0]->media->pluck('id')->toArray());
        $this->assertTrue($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('variants'));

        $collection = new MediableCollection([SampleMediable::first()]);
        $this->assertSame($collection, $collection->loadMediaWithVariantsMatchAll(['foo', 'bar']));
        $this->assertTrue($collection[0]->relationLoaded('media'));
        $this->assertEquals([2, 2], $collection[0]->media->pluck('id')->toArray());
        $this->assertTrue($collection[0]->media[0]->relationLoaded('originalMedia'));
        $this->assertTrue($collection[0]->media[0]->relationLoaded('variants'));
    }
}