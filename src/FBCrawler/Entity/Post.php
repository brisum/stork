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
     * @var string
     *
     * @ORM\Column(name="image_url", type="text")
     */
    private $imageUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="text")
     */
    private $image;

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

    public function getReportId(): ?string
    {
        return $this->reportId;
    }

    public function setReportId(string $reportId): self
    {
        $this->reportId = $reportId;

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

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
