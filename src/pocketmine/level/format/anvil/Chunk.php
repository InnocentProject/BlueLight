<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\level\format\anvil;

use pocketmine\level\format\generic\BaseChunk;
use pocketmine\level\format\generic\EmptyChunkSection;
use pocketmine\level\format\LevelProvider;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;

class Chunk extends BaseChunk{

	/** @var Compound */
	protected $nbt;

	public function __construct(LevelProvider $level, Compound $nbt){
		$this->nbt = $nbt;

		if($this->nbt->Entities instanceof Enum){
			$this->nbt->Entities->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->Entities = new Enum("Entities", []);
			$this->nbt->Entities->setTagType(NBT::TAG_Compound);
		}

		if($this->nbt->TileEntities instanceof Enum){
			$this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->TileEntities = new Enum("TileEntities", []);
			$this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
		}

		if($this->nbt->TileTicks instanceof Enum){
			$this->nbt->TileTicks->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->TileTicks = new Enum("TileTicks", []);
			$this->nbt->TileTicks->setTagType(NBT::TAG_Compound);
		}

		if($this->nbt->Sections instanceof Enum){
			$this->nbt->Sections->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->Sections = new Enum("Sections", []);
			$this->nbt->Sections->setTagType(NBT::TAG_Compound);
		}

		$sections = [];
		foreach($this->nbt->Sections as $section){
			if($section instanceof Compound){
				$sections[(int) $section["Y"]] = new ChunkSection($section);
			}
		}

		parent::__construct($level, $this->nbt["xPos"], $this->nbt["zPos"], $sections, $this->nbt["Entities"], $this->nbt["TileEntities"]);
	}

	public function getChunkSnapshot($includeMaxBlockY = true, $includeBiome = false, $includeBiomeTemp = false){
		$blockId = "";
		$blockData = "";
		$blockSkyLight = "";
		$blockLight = "";
		$emptySections = [false, false, false, false, false, false, false, false];

		$emptyBlocks = str_repeat("\x00", 4096);
		$emptyHalf = str_repeat("\x00", 2048);

		foreach($this->sections as $i => $section){
			if($section instanceof EmptyChunkSection){
				$blockId .= $emptyBlocks;
				$blockData .= $emptyHalf;
				$blockSkyLight .= $emptyHalf;
				$blockLight .= $emptyHalf;
				$emptySections[$i] = true;
			}else{
				$blockId .= $section->getIdArray();
				$blockData .= $section->getDataArray();
				$blockSkyLight .= $section->getSkyLightArray();
				$blockLight .= $section->getLightArray();
			}
		}

		//TODO: maxBlockY, biomeMap, biomeTemp

		//TODO: time
		return new ChunkSnapshot($this->getX(), $this->getZ(), $this->getLevel()->getName(), 0/*$this->getLevel()->getTime()*/, $blockId, $blockData, $blockSkyLight, $blockLight, $emptySections, null, null, null, null);
	}

	/**
	 * @return Compound
	 */
	public function getNBT(){
		return $this->nbt;
	}
}