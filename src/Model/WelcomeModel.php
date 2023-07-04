<?php

namespace App\Model;

class WelcomeModel
{
    const SITE_TITLE_LABEL = 'Titre du blog';
    const SITE_TITLE_NAME = 'blog_title';

    const SITE_INSTALLED_LABEL = 'Site installé';
    const SITE_INSTALLED_NAME = 'blog_installed';

    private ?string $siteTitle;

    private ?string $username;

    private ?string $email;

    private ?string $lastname;

    private ?string $firstname;

    private ?string $address;

    private ?string $zipcode;

    private ?string $city;

    private ?string $password;

        /**
     * @param string|null
     */
    public function getSiteTitle(): ?string
    {
        return $this->siteTitle;
    }

    /**
     * @param string|null $siteTitle
     */
    public function setSiteTitle(?string $siteTitle): void
    {
        $this->siteTitle = $siteTitle;
    }

    /**
     * @param string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @param string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param string|null $lastname
     */
    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @param string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string|null $firstname
     */
    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @param string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @param string|null
     */
    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    /**
     * @param string|null $zipcode
     */
    public function setZipcode(?string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @param string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @param string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }
}
