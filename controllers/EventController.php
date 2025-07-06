<?php
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../core/Response.php';

class EventController {
    public function createEvent($req, $res) {
        try {
            $date = $req['date'] ?? null;
            $time = $req['time'] ?? null;
            $address = $req['address'] ?? null;

            if (!$date || !$time || !$address) {
                return Response::json($res, ['message' => 'All fields are required'], 400);
            }

            if (!preg_match('/^(0?[1-9]|1[0-2]):[0-5][0-9] (AM|PM)$/', $time)) {
                return Response::json($res, ['message' => 'Invalid time format. Use HH:MM AM/PM'], 400);
            }

            $dateCheck = date_create($date);
            if (!$dateCheck) {
                return Response::json($res, ['message' => 'Invalid date format'], 400);
            }

            $eventModel = new Event();
            $success = $eventModel->create([
                'date' => $date,
                'time' => $time,
                'address' => $address
            ]);

            if ($success) {
                return Response::json($res, ['message' => 'Event created successfully']);
            }

            throw new Exception("Failed to save event");
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Failed to create event', 'error' => $e->getMessage()], 500);
        }
    }

    public function getEvents($req, $res) {
        try {
            $eventModel = new Event();
            $events = $eventModel->getAll();
            return Response::json($res, $events);
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Failed to fetch events', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateEvent($req, $res) {
        try {
            $eventId = $req['params']['eventId'];
            $date = $req['date'] ?? null;
            $time = $req['time'] ?? null;
            $address = $req['address'] ?? null;

            $eventModel = new Event();
            $existing = $eventModel->findById($eventId);

            if (!$existing) {
                return Response::json($res, ['message' => 'Event not found'], 404);
            }

            $success = $eventModel->update($eventId, [
                'date' => $date ?? $existing['date'],
                'time' => $time ?? $existing['time'],
                'address' => $address ?? $existing['address']
            ]);

            if ($success) {
                return Response::json($res, ['message' => 'Event updated successfully']);
            }

            throw new Exception("Update failed");
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Failed to update event', 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteEvent($req, $res) {
        try {
            $eventId = $req['params']['eventId'];
            $eventModel = new Event();
            $existing = $eventModel->findById($eventId);

            if (!$existing) {
                return Response::json($res, ['message' => 'Event not found'], 404);
            }

            $eventModel->delete($eventId);
            return Response::json($res, ['message' => 'Event deleted successfully']);
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Failed to delete event', 'error' => $e->getMessage()], 500);
        }
    }
}
