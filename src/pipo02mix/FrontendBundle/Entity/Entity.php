<?php

namespace arrabassada\FrontendBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zona
 *
 * @ORM\Table(name="inmuebles_zonas")
 * @ORM\Entity
 */
class Zona
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="nombre", type="string", length=255)
     */
    private $nombre;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="activa", type="boolean")
     */
    private $activa;

    /**
     * @var string
     *
     * @ORM\OneToMany(targetEntity="Inmueble", mappedBy="zona")
     */
    private $inmuebles;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inmuebles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Zona
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set inmuebles
     *
     * @param string $inmuebles
     * @return Zona
     */
    public function setInmuebles($inmuebles)
    {
        $this->inmuebles = $inmuebles;
    
        return $this;
    }

    /**
     * Get inmuebles
     *
     * @return string 
     */
    public function getInmuebles()
    {
        return $this->inmuebles;
    }
    
    /**
     * Add inmuebles
     *
     * @param \arrabassada\FrontendBundle\Entity\Inmueble $inmuebles
     * @return Zona
     */
    public function addInmueble(\arrabassada\FrontendBundle\Entity\Inmueble $inmuebles)
    {
        $this->inmuebles[] = $inmuebles;
    
        return $this;
    }

    /**
     * Remove inmuebles
     *
     * @param \arrabassada\FrontendBundle\Entity\Inmueble $inmuebles
     */
    public function removeInmueble(\arrabassada\FrontendBundle\Entity\Inmueble $inmuebles)
    {
        $this->inmuebles->removeElement($inmuebles);
    }

    /**
     * Set activa
     *
     * @param boolean $activa
     * @return Zona
     */
    public function setActiva($activa)
    {
        $this->activa = $activa;
    
        return $this;
    }

    /**
     * Get activa
     *
     * @return boolean 
     */
    public function getActiva()
    {
        return $this->activa;
    }
    public function __toString()
    {
        return $this->nombre;
    }
}