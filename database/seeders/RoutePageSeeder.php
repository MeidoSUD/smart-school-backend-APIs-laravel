<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Operations\Entities\PickupPoint;
use Modules\Operations\Entities\TransportRoute;
use Modules\Operations\Entities\VehicleRoute;

/**
 * Seeds the dataset required by the CodeIgniter page `user/route` (index).
 *
 * CI source of truth:
 * - Controller: application/controllers/user/Route.php :: index(), getbusdetail()
 * - Models: Student_model::get($student_id) (students + student_session for the
 *   current session LEFT JOIN vehicle_routes/transport_route/vehicles and
 *   route_pickup_point/pickup_point),
 *   Pickuppoint_model::getPickupPointByRouteID($route_id) (route_pickup_point
 *   JOIN transport_route JOIN pickup_point WHERE transport_route_id ORDER BY
 *   order_number ASC),
 *   Vehroute_model::getVechileDetailByVecRouteID($vehrouteid) (vehicle_routes
 *   JOIN vehicles JOIN transport_route WHERE vehicle_routes.id)
 * - View: application/views/user/route/index.php (route title card: vehicle_no,
 *   vehicle_model, manufacture_year, driver_name/licence/contact, vehicle_photo;
 *   pickup timeline: pickup_point name, destination_distance, pickup_time, with
 *   the student's own pickup_point_name highlighted as active)
 *
 * Dependency order respected here (parents first):
 *   transport_route > vehicles > vehicle_routes,
 *   pickup_point > route_pickup_point > student_session assignment
 *
 * Rows owned by other seeders (FeeSeeder's route/point, OnlineAdmissionSeeder's
 * vehicle link, DashboardSupportSeeder's fees) are reused, never recreated and
 * never have their fees touched. Only NULL/0 student transport assignments are
 * filled. Safe to run repeatedly (firstOrCreate / updateOrCreate, no truncate).
 */
class RoutePageSeeder extends Seeder
{
    public function run(): void
    {
        // ---- 1. Transport routes (reuse FeeSeeder's "الطريق الرئيسي") ----
        $mainRoute = TransportRoute::firstOrCreate(
            ['route_title' => 'الطريق الرئيسي'],
            ['no_of_vehicle' => 2, 'note' => '', 'is_active' => 'yes']
        );

        $northRoute = TransportRoute::firstOrCreate(
            ['route_title' => 'الطريق الشمالي'],
            ['no_of_vehicle' => 2, 'note' => 'يخدم الأحياء الشمالية', 'is_active' => 'yes']
        );

        $southRoute = TransportRoute::firstOrCreate(
            ['route_title' => 'الطريق الجنوبي'],
            ['no_of_vehicle' => 1, 'note' => 'يخدم الأحياء الجنوبية', 'is_active' => 'yes']
        );

        // ---- 2. Vehicles (reuse OnlineAdmissionSeeder's ABC 1234) ----
        $vehicles = [
            [
                'vehicle_no' => 'ABC 1234',
                'vehicle_model' => 'Toyota Hiace',
                'manufacture_year' => '2023',
                'registration_number' => 'REG-001',
                'chasis_number' => 'CHS-001',
                'max_seating_capacity' => '15',
                'driver_name' => 'محمد السالم',
                'driver_licence' => 'LIC-001',
                'driver_contact' => '0555777700',
            ],
            [
                'vehicle_no' => 'XYZ 5678',
                'vehicle_model' => 'Hyundai County',
                'manufacture_year' => '2022',
                'registration_number' => 'REG-002',
                'chasis_number' => 'CHS-002',
                'max_seating_capacity' => '25',
                'driver_name' => 'عبدالله الدوسري',
                'driver_licence' => 'LIC-002',
                'driver_contact' => '0555888800',
            ],
            [
                'vehicle_no' => 'KLM 9012',
                'vehicle_model' => 'Toyota Coaster',
                'manufacture_year' => '2021',
                'registration_number' => 'REG-003',
                'chasis_number' => 'CHS-003',
                'max_seating_capacity' => '30',
                'driver_name' => 'سعد القحطاني',
                'driver_licence' => 'LIC-003',
                'driver_contact' => '0555999900',
            ],
            [
                'vehicle_no' => 'NOP 3456',
                'vehicle_model' => 'Mitsubishi Rosa',
                'manufacture_year' => '2020',
                'registration_number' => 'REG-004',
                'chasis_number' => 'CHS-004',
                'max_seating_capacity' => '25',
                'driver_name' => 'فهد العتيبي',
                'driver_licence' => 'LIC-004',
                'driver_contact' => '0555111222',
            ],
        ];

        $vehicleIds = [];
        foreach ($vehicles as $vehicle) {
            $existingId = DB::table('vehicles')->where('vehicle_no', $vehicle['vehicle_no'])->value('id');
            $vehicleIds[] = $existingId ?? DB::table('vehicles')->insertGetId(
                $vehicle + ['vehicle_photo' => null, 'note' => '', 'created_at' => now()]
            );
        }

        // ---- 3. Vehicle ↔ route links (one vehicle may serve a route; the main
        //        route gets two buses like a real school) ----
        $links = [
            [$mainRoute->id, $vehicleIds[0]],
            [$mainRoute->id, $vehicleIds[1]],
            [$northRoute->id, $vehicleIds[2]],
            [$southRoute->id, $vehicleIds[3]],
        ];

        $vehrouteIdsByRoute = [];
        foreach ($links as [$routeId, $vehicleId]) {
            $vehroute = VehicleRoute::firstOrCreate(
                ['route_id' => $routeId, 'vehicle_id' => $vehicleId]
            );
            $vehrouteIdsByRoute[$routeId][] = $vehroute->id;
        }

        // ---- 4. Pickup points ----
        $pointNames = [
            'المدرسة', 'حي النزهة', 'حي العليا', 'حي الملقا',
            'حي الشاطئ', 'حي الربيع', 'حي السعادة', 'حي الروضة',
        ];

        $pointIds = [];
        foreach ($pointNames as $name) {
            $pointIds[$name] = PickupPoint::firstOrCreate(['name' => $name])->id;
        }

        // ---- 5. Route stops (ordered timelines; every route ends at school) ----
        //        Existing FeeSeeder stop (route 1 ↔ المدرسة) keeps its fees value.
        $stops = [
            $mainRoute->id => [
                ['point' => 'حي النزهة', 'fees' => 450.00, 'distance' => 4.5, 'time' => '06:45:00', 'order' => 1],
                ['point' => 'حي العليا', 'fees' => 400.00, 'distance' => 3.0, 'time' => '06:55:00', 'order' => 2],
                ['point' => 'حي الروضة', 'fees' => 350.00, 'distance' => 1.5, 'time' => '07:05:00', 'order' => 3],
                ['point' => 'المدرسة', 'fees' => null, 'distance' => 0.0, 'time' => '07:15:00', 'order' => 4],
            ],
            $northRoute->id => [
                ['point' => 'حي الملقا', 'fees' => 500.00, 'distance' => 6.0, 'time' => '06:40:00', 'order' => 1],
                ['point' => 'حي الربيع', 'fees' => 450.00, 'distance' => 4.0, 'time' => '06:52:00', 'order' => 2],
                ['point' => 'المدرسة', 'fees' => null, 'distance' => 0.0, 'time' => '07:15:00', 'order' => 3],
            ],
            $southRoute->id => [
                ['point' => 'حي الشاطئ', 'fees' => 550.00, 'distance' => 7.5, 'time' => '06:35:00', 'order' => 1],
                ['point' => 'حي السعادة', 'fees' => 400.00, 'distance' => 2.5, 'time' => '07:00:00', 'order' => 2],
                ['point' => 'المدرسة', 'fees' => null, 'distance' => 0.0, 'time' => '07:15:00', 'order' => 3],
            ],
        ];

        $rppIdsByRoute = [];
        foreach ($stops as $routeId => $routeStops) {
            foreach ($routeStops as $stop) {
                $existing = DB::table('route_pickup_point')
                    ->where('transport_route_id', $routeId)
                    ->where('pickup_point_id', $pointIds[$stop['point']])
                    ->first();

                if ($existing) {
                    // Never touch fees (owned by fee seeders); only ensure the
                    // timeline fields the CI view prints are populated.
                    $patch = [];
                    if ($existing->destination_distance == 0 && $stop['distance'] != 0) {
                        $patch['destination_distance'] = $stop['distance'];
                    }
                    if (empty($existing->pickup_time) && ! empty($stop['time'])) {
                        $patch['pickup_time'] = $stop['time'];
                    }
                    if ($existing->order_number != $stop['order']) {
                        $patch['order_number'] = $stop['order'];
                    }
                    if (! empty($patch)) {
                        DB::table('route_pickup_point')->where('id', $existing->id)->update($patch);
                    }
                    $rppIdsByRoute[$routeId][] = $existing->id;
                    continue;
                }

                $rppIdsByRoute[$routeId][] = DB::table('route_pickup_point')->insertGetId([
                    'transport_route_id' => $routeId,
                    'pickup_point_id' => $pointIds[$stop['point']],
                    'fees' => $stop['fees'] ?? 0.00,
                    'destination_distance' => $stop['distance'],
                    'pickup_time' => $stop['time'],
                    'order_number' => $stop['order'],
                    'created_at' => now(),
                ]);
            }
        }

        // ---- 6. Assign transport to students: only fill missing links so the
        //        CI page (and Laravel RouteController@index) returns real data.
        //        Students are spread across routes; each gets a stop of its own
        //        route so pickup_point_name highlighting works. ----
        $routeIds = [$mainRoute->id, $northRoute->id, $southRoute->id];

        $students = DB::table('student_session')->orderBy('id')->get();
        foreach ($students as $index => $ss) {
            $vehrouteId = $ss->vehroute_id;
            $routeId = null;

            if (! empty($vehrouteId)) {
                $routeId = DB::table('vehicle_routes')->where('id', $vehrouteId)->value('route_id');
            }

            if (empty($vehrouteId) || empty($routeId)) {
                $routeId = $routeIds[$index % count($routeIds)];
                $vehroutes = $vehrouteIdsByRoute[$routeId];
                $vehrouteId = $vehroutes[$index % count($vehroutes)];
            }

            $patch = [];
            if (empty($ss->vehroute_id)) {
                $patch['vehroute_id'] = $vehrouteId;
            }
            if (empty($ss->route_pickup_point_id)) {
                $routeStops = $rppIdsByRoute[$routeId];
                // Prefer a non-school stop so the timeline highlight is visible.
                $nonSchool = array_values(array_filter($routeStops, function ($id) use ($pointIds) {
                    $pid = DB::table('route_pickup_point')->where('id', $id)->value('pickup_point_id');
                    return $pid != $pointIds['المدرسة'];
                }));
                $pool = ! empty($nonSchool) ? $nonSchool : $routeStops;
                $patch['route_pickup_point_id'] = $pool[$index % count($pool)];
            }

            if (! empty($patch)) {
                DB::table('student_session')->where('id', $ss->id)->update($patch);
            }
        }
    }
}
