<?php

namespace Mijora\BoxCalculator\Methods;

use Mijora\BoxCalculator\Methods\Core;

class Heuristic3D extends Core
{
    /**
     * Placed items as flat arrays: [x, y, z, x2, y2, z2]
     */
    private $placed_items = array();

    /**
     * Candidate placement points [x, y, z].
     * Only extreme points (corners of placed items) are tested.
     */
    private $extreme_points = array();

    /** Incrementally tracked box bounds */
    private $cur_w = 0;
    private $cur_h = 0;
    private $cur_l = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function findMinBoxSize()
    {
        $this->debug->add("The method used does not have the ability to calculate the minimum box size");
        return $this->box;
    }

    public function findBoxSizeUntilMaxSize()
    {
        if ( ! $this->box_max_size ) {
            $this->debug->add("Maximum box size not specified");
            return false;
        }

        $this->items = $this->sortItemsByVolume($this->items);

        // Reset state
        $this->placed_items = [];
        $this->extreme_points = [[0, 0, 0]];
        $this->cur_w = 0;
        $this->cur_h = 0;
        $this->cur_l = 0;

        $max_w = $this->box->getMaxWidth() - $this->wall_thickness;
        $max_h = $this->box->getMaxHeight() - $this->wall_thickness;
        $max_l = $this->box->getMaxLength() - $this->wall_thickness;

        foreach ( $this->items as $item_id => $item ) {
            $this->debug->add('Adding item #' . $item_id . ': ' . $this->debug->obj($item));
            $this->debug->add("Current box size: " . $this->debug->obj($this->box));

            $rotations = $this->getUniqueRotations(
                $item->getWidth(), $item->getHeight(), $item->getLength()
            );

            // Sort extreme points — prefer positions closer to origin for tighter packing
            usort($this->extreme_points, function ($a, $b) {
                return ($a[1] + $a[0] + $a[2]) <=> ($b[1] + $b[0] + $b[2]);
            });

            $best_pos = null;
            $best_rot = null;
            $best_vol = PHP_INT_MAX;

            foreach ( $this->extreme_points as $ep ) {
                $px = $ep[0];
                $py = $ep[1];
                $pz = $ep[2];

                foreach ( $rotations as $rot ) {
                    $rw = $rot[0];
                    $rh = $rot[1];
                    $rl = $rot[2];

                    // Quick bounds check before expensive collision test
                    if ( $px + $rw > $max_w || $py + $rh > $max_h || $pz + $rl > $max_l ) {
                        continue;
                    }

                    if ( ! $this->hasCollision($px, $py, $pz, $rw, $rh, $rl) ) {
                        // Pick placement that minimises resulting box volume
                        $nw = max($this->cur_w, $px + $rw);
                        $nh = max($this->cur_h, $py + $rh);
                        $nl = max($this->cur_l, $pz + $rl);
                        $vol = $nw * $nh * $nl;

                        if ( $vol < $best_vol ) {
                            $best_vol = $vol;
                            $best_pos = $ep;
                            $best_rot = $rot;
                        }
                    }
                }
            }

            if ( $best_pos !== null ) {
                $px = $best_pos[0];
                $py = $best_pos[1];
                $pz = $best_pos[2];
                $rw = $best_rot[0];
                $rh = $best_rot[1];
                $rl = $best_rot[2];

                $x2 = $px + $rw;
                $y2 = $py + $rh;
                $z2 = $pz + $rl;

                $this->debug->add('Found suitable position: ' . $px . ' - ' . $py . ' - ' . $pz);

                // Store with pre-computed end coordinates
                $this->placed_items[] = [$px, $py, $pz, $x2, $y2, $z2];

                // Generate new candidate placement points
                $this->addExtremePoints($px, $py, $pz, $rw, $rh, $rl);

                // Update box bounds incrementally
                $this->cur_w = max($this->cur_w, $x2);
                $this->cur_h = max($this->cur_h, $y2);
                $this->cur_l = max($this->cur_l, $z2);

                $this->box = $this->updateBox($this->cur_w, $this->cur_h, $this->cur_l);
                $this->debug->add("New box size: " . $this->debug->obj($this->box));
                $this->debug->end('ITEM ADD');
            } else {
                $this->debug->add("Failed to insert item #" . $item_id);
                $this->debug->add("Positions of inserted items: " . print_r($this->placed_items, true));
                $this->debug->end('ITEM ADD');
                return false;
            }
        }

        return $this->box;
    }

    /**
     * Return unique rotation tuples [w, h, l].
     */
    private function getUniqueRotations($w, $h, $l)
    {
        $all = [
            [$w, $h, $l],
            [$w, $l, $h],
            [$h, $w, $l],
            [$h, $l, $w],
            [$l, $w, $h],
            [$l, $h, $w],
        ];

        $unique = [];
        $seen = [];
        foreach ( $all as $r ) {
            $key = $r[0] . ',' . $r[1] . ',' . $r[2];
            if ( ! isset($seen[$key]) ) {
                $seen[$key] = true;
                $unique[] = $r;
            }
        }

        return $unique;
    }

    /**
     * Add new extreme points after placing an item and prune invalid ones.
     */
    private function addExtremePoints($x, $y, $z, $w, $h, $l)
    {
        $this->extreme_points[] = [$x + $w, $y, $z];
        $this->extreme_points[] = [$x, $y + $h, $z];
        $this->extreme_points[] = [$x, $y, $z + $l];

        // Deduplicate and remove points inside placed items
        $cleaned = [];
        $seen = [];

        foreach ( $this->extreme_points as $ep ) {
            $key = $ep[0] . ',' . $ep[1] . ',' . $ep[2];
            if ( isset($seen[$key]) ) {
                continue;
            }
            $seen[$key] = true;

            $inside = false;
            foreach ( $this->placed_items as $p ) {
                if ( $ep[0] >= $p[0] && $ep[0] < $p[3] &&
                     $ep[1] >= $p[1] && $ep[1] < $p[4] &&
                     $ep[2] >= $p[2] && $ep[2] < $p[5] ) {
                    $inside = true;
                    break;
                }
            }

            if ( ! $inside ) {
                $cleaned[] = $ep;
            }
        }

        $this->extreme_points = $cleaned;
    }

    /**
     * AABB collision test using pre-computed end coordinates.
     */
    private function hasCollision($x, $y, $z, $w, $h, $l)
    {
        $x2 = $x + $w;
        $y2 = $y + $h;
        $z2 = $z + $l;

        foreach ( $this->placed_items as $p ) {
            if ( $x < $p[3] && $x2 > $p[0] &&
                 $y < $p[4] && $y2 > $p[1] &&
                 $z < $p[5] && $z2 > $p[2] ) {
                return true;
            }
        }

        return false;
    }
}
