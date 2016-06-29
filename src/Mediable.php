<?php

namespace Frasmage\Mediable;

use Illuminate\Database\Eloquent\Builder;

/**
 * HasMedia Trait
 *
 */
trait Mediable
{

    /**
     * @see HasMediaInterface::media()
     */
    public function media()
    {
        return $this->morphToMany(Media::class, 'mediable')->withPivot('association');
    }

    /**
     * Query scope to detect the presence of one or more attached media for a particular association
     * @param  Builder $q
     * @param  string  $association
     */
    public function scopeWhereHasMedia(Builder $q, $association)
    {
        $q->whereHas('media', function ($q) use ($association) {
            $q->where('association', '=', $association);
        });
    }

    /**
     * @see HasMediaInterface::addMedia()
     */
    public function addMedia($media_id, $association)
    {
        $this->media()->attach($media_id, ['association' => $association]);
    }

    /**
     * @see HasMediaInterface::replaceMedia()
     */
    public function syncMedia($media_array, $association)
    {
        $model = $this;
        $this->removeMediaForAssociation($association);
        $model->addMedia(collect($media_array), $association);
    }

    /**
     * @see HasMediaInterface::removeMedia()
     */
    public function removeMedia($media_id, $association = null)
    {
        if ($media_id instanceof Media) {
            $media_id = $media_id->id;
        }
        $query = $this->media();
        if ($association) {
            $query->where('association', $association);
        }
        $query->detach($media_id);
    }

    /**
     * @see HasMediaInterface::removeMediaForAssociation()
     */
    public function removeMediaForAssociation($association)
    {
        $model = $this;
        $this->getMedia($association)->each(function ($media) use ($model, $association) {
            $model->removeMedia($media->id, $association);
        });
    }

    /**
     * @see HasMediaInterface::hasMedia()
     */
    public function hasMedia($association)
    {
        return count($this->getMedia($association)) > 0;
    }

    /**
     * @see HasMediaInterface::getMedia()
     */
    public function getMedia($association)
    {
        $model = $this;
        return $this->media->filter(function ($media) use ($model, $association) {
            return $media->pivot->association == $association;
        });
    }

    /**
     * @see HasMediaInterface::firstMedia()
     */
    public function firstMedia($association)
    {
        return $this->getMedia($association)->first();
    }

    /**
     * @see HasMediaInterface::getAllMedia()
     */
    public function getAllMedia()
    {
        return $this->media->groupBy('association');
    }
}
