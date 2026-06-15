<?php

namespace App\Support;

use App\Models\User;

final class ModuleCatalog
{
    /**
     * @return array<string, array{label: string, description: string}>
     */
    public static function all(): array
    {
        return [
            'dashboard' => [
                'label' => 'Inicio',
                'description' => 'Panel principal y agenda general.',
            ],
            'appointments' => [
                'label' => 'Citas',
                'description' => 'Gestion de citas medicas.',
            ],
            'services' => [
                'label' => 'Servicios',
                'description' => 'Catalogo de servicios de la clinica.',
            ],
            'patients' => [
                'label' => 'Pacientes',
                'description' => 'Registro y gestion de pacientes.',
            ],
            'doctor_schedules' => [
                'label' => 'Horarios',
                'description' => 'Disponibilidad y horarios de doctores.',
            ],
            'doctors' => [
                'label' => 'Doctores',
                'description' => 'Registro y gestion de doctores.',
            ],
            'history' => [
                'label' => 'Historial',
                'description' => 'Consulta del historial clinico.',
            ],
            'reports' => [
                'label' => 'Reportes',
                'description' => 'Consulta de reportes administrativos.',
            ],
            'configuration' => [
                'label' => 'Configuracion',
                'description' => 'Administracion de usuarios y permisos.',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return list<string>
     */
    public static function defaultsForRole(string $role): array
    {
        return match ($role) {
            'administrador' => self::keys(),
            'jefe_area' => ['dashboard', 'services', 'patients', 'doctors', 'configuration'],
            'doctor' => ['dashboard', 'appointments', 'patients', 'doctor_schedules', 'history'],
            'asistente' => ['dashboard', 'appointments', 'patients'],
            'contador' => ['dashboard', 'reports'],
            default => ['dashboard'],
        };
    }

    /**
     * @return list<array{value: string, label: string, description: string}>
     */
    public static function options(): array
    {
        return collect(self::all())
            ->map(fn (array $module, string $key) => [
                'value' => $key,
                'label' => $module['label'],
                'description' => $module['description'],
            ])
            ->values()
            ->all();
    }

    public static function firstAccessibleRoute(User $user): ?string
    {
        $routes = [
            'dashboard' => 'dashboard',
            'appointments' => 'appointments.index',
            'services' => 'services.index',
            'patients' => 'patients.index',
            'doctor_schedules' => 'doctor-schedules.index',
            'doctors' => 'doctors.index',
            'history' => 'history.index',
            'reports' => 'reports.index',
            'configuration' => 'users.index',
        ];

        foreach ($routes as $module => $routeName) {
            if ($user->hasModuleAccess($module)) {
                return $routeName;
            }
        }

        return null;
    }
}
