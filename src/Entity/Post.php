<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
class Post extends BaseEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Поле обязательно для заполнения")
     * @Assert\Type(type="string", message="Неверный тип данных")
     * @Assert\Length(max="255", maxMessage="Максимальное количество символов в строке 255")
     */
    private $title;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime(message="Это значение не является валидным датой и временем")
     */
    private $published_at;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Assert\NotBlank(message="Поле обязательно для заполнения")
     * @Assert\Type(type="bool", message="Неверный тип данных")
     */
    private $published;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $preview_text;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank(message="Поле обязательно для заполнения")
     * @Assert\Type(type="string", message="Неверный тип данных")
     */
    private $text;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $author;

    /**
     * Many Posts Has Many Categories
     * @ORM\ManyToMany(targetEntity="Category")
     * @ORM\JoinTable(name="posts_categories",
     *     joinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")}
     * )
     * @Assert\Type(type="object", message="Неверный тип данных")
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return Post
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param boolean|null $published
     * @return Post
     */
    public function setPublished(?bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getPublished(): ?bool
    {
        return boolval($this->published);
    }

    /**
     * @param DateTime|null $published_at
     * @return Post
     */
    public function setPublishedAt(?DateTime $published_at): self
    {
        $this->published_at = $published_at;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPublishedAt(): ?DateTime
    {
        return $this->published_at;
    }

    /**
     * @param string|null $text
     * @return Post
     */
    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string|null $preview_text
     * @return Post
     */
    public function setPreviewText(?string $preview_text): self
    {
        $this->preview_text = $preview_text;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPreviewText(): ?string
    {
        return $this->preview_text;
    }

    /**
     * @param string|null $author
     * @return Post
     */
    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function addCategory(Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    public function resetCategories()
    {
        $this->categories = [];
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatePublishedAt()
    {
        if ($this->getPublished() === null) {
            $this->published = false;
        }

        if($this->getPublished() === true && $this->getPublishedAt() === null) {
            $this->setPublishedAt(new DateTime('now'));
        }
    }

}
