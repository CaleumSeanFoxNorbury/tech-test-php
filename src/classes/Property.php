<?php

class Property implements JsonSerializable {

    private string $title;
    private string $address;
    private float $lat;
    private float $long;
    private array $amenities;
    private string $soldBy;
    private int $bedrooms;
    private int $bathrooms;
    private int $receptionRooms;
    private string $image;
    private string $price;

    public function __construct(string $title, string $address, float $lat, float $long, array $amenities, string $soldBy, int $bedrooms, int $bathrooms, int $receptionRooms, string $image, string $price) {
        $this->title = $title;
        $this->address = $address;
        $this->lat = $lat;
        $this->long = $long;
        $this->amenities = $amenities;
        $this->soldBy = $soldBy;
        $this->bedrooms = $bedrooms;
        $this->bathrooms = $bathrooms;
        $this->receptionRooms = $receptionRooms;
        $this->image = $image;
        $this->price = $price;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function getAddress(): string {
        return $this->address;
    }

    public function setAddress(string $address): void {
        $this->address = $address;
    }

    public function getLat(): float {
        return $this->lat;
    }

    public function setLat(float $lat): void {
        $this->lat = $lat;
    }

    public function getLong(): float {
        return $this->long;
    }

    public function setLong(float $long): void {
        $this->long = $long;
    }

    public function getAmenities(): array {
        return $this->amenities;
    }

    public function setAmenities(array $amenities): void {
        $this->amenities = $amenities;
    }

    public function getSoldBy(): string {
        return $this->soldBy;
    }

    public function setSoldBy(string $soldBy): void {
        $this->soldBy = $soldBy;
    }

    public function getBedrooms(): int {
        return $this->bedrooms;
    }

    public function setBedrooms(int $bedrooms): void {
        $this->bedrooms = $bedrooms;
    }

    public function getBathrooms(): int {
        return $this->bathrooms;
    }

    public function setBathrooms(int $bathrooms): void {
        $this->bathrooms = $bathrooms;
    }

    public function getReceptionRooms(): int {
        return $this->receptionRooms;
    }

    public function setReceptionRooms(int $receptionRooms): void {
        $this->receptionRooms = $receptionRooms;
    }

    public function getImage(): string {
        return $this->image;
    }

    public function setImage(string $image): void {
        $this->image = $image;
    }

    public function getPrice(): string {
        return $this->price;
    }

    public function setPrice(string $price): void {
        $this->price = $price;
    }

    /**
     * allow serialization of private data 
     */
    public function jsonSerialize(): mixed {
        return [
            'title' => $this->title,
            'address' => $this->address,
            'lat' => $this->lat,
            'long' => $this->long,
            'amenities' => $this->amenities,
            'soldBy' => $this->soldBy,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'receptionRooms' => $this->receptionRooms,
            'image' => $this->image,
            'price' => $this->price
        ];
    }
}

?>