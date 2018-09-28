<?php

namespace Brisum\FBCrawler\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Post
 *
 * @ORM\Table(
 *      name="post",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="report_id", columns={"report_id"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Brisum\FBCrawler\Repository\PostRepository")
 */
class Post
{
    const TYPE_PHOTO = 'photo';
    const TYPE_CAROUSEL= 'carousel';
    const TYPE_UNKNOWN = null;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Company
     *
     * @ORM\ManyToOne(targetEntity="Brisum\FBCrawler\Entity\Company", inversedBy="posts")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="report_id", type="string", length=100, nullable=false)
     */
    private $reportId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=10, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", length=1000)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="subtitle", type="text", length=1000)
     */
    private $subtitle;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var array
     *
     * @ORM\Column(name="data", type="array")
     */
    private $data;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish_time", type="datetime")
     */
    private $publishTime;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;



    public function __toString()
    {
        $company = (string) $this->getCompany();
        $title = $this->getTitle();
        return ($company ? "{$company}: " : '') . ($title ? $title : $this->getSubtitle());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getReportId(): ?string
    {
        return $this->reportId;
    }

    public function setReportId(string $reportId): self
    {
        $this->reportId = $reportId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getPublishTime(): ?\DateTimeInterface
    {
        return $this->publishTime;
    }

    public function setPublishTime(\DateTimeInterface $publishTime): self
    {
        $this->publishTime = $publishTime;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }
}
