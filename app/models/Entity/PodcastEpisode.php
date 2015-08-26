<?php
namespace Entity;

use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @Table(name="podcast_episodes")
 * @Entity
 */
class PodcastEpisode extends \DF\Doctrine\Entity
{
    public function __construct()
    {
        $this->is_notified = false;
        $this->is_active = true;
        $this->play_count = 0;
    }

    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /** @Column(name="podcast_id", type="integer") */
    protected $podcast_id;

    /** @Column(name="source_id", type="integer", nullable=true) */
    protected $source_id;

    /** @Column(name="guid", type="string", length=128, nullable=true) */
    protected $guid;

    /** @Column(name="timestamp", type="integer") */
    protected $timestamp;

    /** @Column(name="title", type="string", length=400, nullable=true) */
    protected $title;

    /** @Column(name="body", type="text", nullable=true) */
    protected $body;

    /** @Column(name="summary", type="text", nullable=true) */
    protected $summary;

    /** @Column(name="web_url", type="string", length=255, nullable=true) */
    protected $web_url;

    public function getPlayerUrl()
    {
        return self::getEpisodePlayerUrl($this->web_url);
    }

    /** @Column(name="thumbnail_url", type="string", length=255, nullable=true) */
    protected $thumbnail_url;

    public function getThumbnail()
    {
        if ($this->thumbnail_url)
        {
            return $this->thumbnail_url;
        }
        else
        {
            $source_type = $this->source->type;
            return \DF\Url::content('images/podcast_'.$source_type.'.png');
        }
    }

    /** @Column(name="banner_url", type="string", length=255, nullable=true) */
    protected $banner_url;

    /** @Column(name="is_notified", type="boolean") */
    protected $is_notified;

    /** @Column(name="is_active", type="boolean") */
    protected $is_active;

    /** @Column(name="play_count", type="integer") */
    protected $play_count;

    /**
     * @ManyToOne(targetEntity="Podcast", inversedBy="episodes")
     * @JoinColumns({
     *   @JoinColumn(name="podcast_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $podcast;

    /**
     * @ManyToOne(targetEntity="PodcastSource", inversedBy="episodes")
     * @JoinColumns({
     *   @JoinColumn(name="source_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $source;

    public function getLocalUrl($origin = NULL)
    {
        return self::getEpisodeLocalUrl($this, $origin);
    }

    /* Static Functions */

    /**
     * @param $row
     * @return array
     */
    public static function api($row)
    {
        if ($row instanceof self)
            $row = $row->toArray();

        $local_url = self::getEpisodeLocalUrl($row, 'api');
        $web_url = $row['web_url'];

        unset($row['podcast_id'], $row['is_notified'], $row['web_url']);

        $row['raw_url'] = $local_url;
        $row['web_url'] = $web_url;

        return $row;
    }

    public static function getEpisodeLocalUrl($row, $origin = NULL)
    {
        return \DF\Url::route(array(
            'module'    => 'default',
            'controller' => 'show',
            'action'    => 'episode',
            'id'        => $row['podcast_id'],
            'episode'   => $row['id'],
            'origin'    => $origin,
        ));
    }

    public static function getEpisodePlayerUrl($web_url)
    {
        // Special handling for SoundCloud URLs.
        if (stristr($web_url, 'soundcloud.com'))
            return 'https://w.soundcloud.com/player/?'.http_build_query(array('auto_play' => 'true', 'url' => $web_url));
        else
            return $web_url;
    }
}