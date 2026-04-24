// routeOptimizer.js - Advanced route optimization with AI/ML capabilities

import { eventBus, EVENTS } from '../utils/eventBus.js';
import { apiClient } from '../modules/api.js';
import { appState } from '../modules/state.js';
import { performanceMonitor } from '../utils/performanceMonitor.js';

class RouteOptimizer {
    constructor() {
        this.optimizationCache = new Map();
        this.trafficData = new Map();
        this.historicalData = new Map();
        this.vehicleProfiles = new Map();
        this.constraints = {
            maxRouteTime: 8 * 60 * 60, // 8 hours in seconds
            maxStops: 20,
            timeWindowBuffer: 15 * 60, // 15 minutes buffer
            trafficMultiplier: 1.3 // Account for traffic delays
        };
        
        this.init();
    }

    init() {
        this.loadVehicleProfiles();
        this.setupTrafficMonitoring();
        console.log('🧠 Advanced route optimizer initialized');
    }

    /**
     * Load vehicle profiles with capacity and constraints
     */
    async loadVehicleProfiles() {
        try {
            const profiles = await apiClient.get('/api/vehicles/profiles');
            
            profiles.data.forEach(profile => {
                this.vehicleProfiles.set(profile.id, {
                    id: profile.id,
                    name: profile.name,
                    capacity: profile.capacity || 50, // kg
                    maxStops: profile.max_stops || 15,
                    avgSpeed: profile.avg_speed || 40, // km/h
                    fuelEfficiency: profile.fuel_efficiency || 12, // km/l
                    operatingCost: profile.operating_cost || 0.5, // per km
                    timeWindows: profile.time_windows || { start: '08:00', end: '18:00' }
                });
            });
            
            console.log(`📋 Loaded ${this.vehicleProfiles.size} vehicle profiles`);
        } catch (error) {
            this.setDefaultVehicleProfile();
        }
    }

    /**
     * Set default vehicle profile
     */
    setDefaultVehicleProfile() {
        this.vehicleProfiles.set('default', {
            id: 'default',
            name: 'Standard Vehicle',
            capacity: 50,
            maxStops: 15,
            avgSpeed: 35,
            fuelEfficiency: 12,
            operatingCost: 0.5,
            timeWindows: { start: '08:00', end: '18:00' }
        });
    }

    /**
     * Setup real-time traffic monitoring
     */
    setupTrafficMonitoring() {
        // Update traffic data every 10 minutes
        setInterval(() => {
            this.updateTrafficData();
        }, 10 * 60 * 1000);
        
        // Initial traffic data load
        this.updateTrafficData();
    }

    /**
     * Update traffic data from various sources
     */
    async updateTrafficData() {
        try {
            const trafficResponse = await apiClient.get('/api/traffic/current');
            
            trafficResponse.data.forEach(segment => {
                this.trafficData.set(segment.segment_id, {
                    congestionLevel: segment.congestion_level, // 0-1 scale
                    avgSpeed: segment.avg_speed,
                    incidents: segment.incidents || [],
                    lastUpdated: Date.now()
                });
            });
            
            eventBus.emit(EVENTS.TRAFFIC_DATA_UPDATED, { 
                segments: this.trafficData.size 
            });
            
        } catch (error) {
            // Silently use time-based traffic estimation
        }
    }

    /**
     * Optimize route with advanced algorithms
     */
    async optimizeRoute(pickups, options = {}) {
        performanceMonitor.startTimer('route_optimization');
        
        try {
            const {
                vehicleId = 'default',
                startTime = new Date(),
                prioritizeTime = true,
                considerTraffic = true,
                allowReordering = true,
                maxDuration = this.constraints.maxRouteTime
            } = options;

            // Get vehicle profile
            const vehicle = this.vehicleProfiles.get(vehicleId) || this.vehicleProfiles.get('default');
            
            // Prepare pickup data with constraints
            const enrichedPickups = await this.enrichPickupData(pickups);
            
            // Check cache first
            const cacheKey = this.generateCacheKey(enrichedPickups, options);
            if (this.optimizationCache.has(cacheKey)) {
                const cached = this.optimizationCache.get(cacheKey);
                if (Date.now() - cached.timestamp < 5 * 60 * 1000) { // 5 minutes
                    performanceMonitor.endTimer('route_optimization');
                    return cached.result;
                }
            }

            // Run optimization algorithm
            let optimizedRoute;
            
            if (enrichedPickups.length <= 3) {
                // Simple optimization for small routes
                optimizedRoute = await this.simpleOptimization(enrichedPickups, vehicle, options);
            } else if (enrichedPickups.length <= 10) {
                // Genetic algorithm for medium routes
                optimizedRoute = await this.geneticAlgorithmOptimization(enrichedPickups, vehicle, options);
            } else {
                // Hybrid approach for large routes
                optimizedRoute = await this.hybridOptimization(enrichedPickups, vehicle, options);
            }

            // Apply traffic adjustments
            if (considerTraffic) {
                optimizedRoute = await this.applyTrafficAdjustments(optimizedRoute);
            }

            // Calculate detailed metrics
            optimizedRoute.metrics = await this.calculateRouteMetrics(optimizedRoute, vehicle);
            
            // Cache the result
            this.optimizationCache.set(cacheKey, {
                result: optimizedRoute,
                timestamp: Date.now()
            });

            performanceMonitor.endTimer('route_optimization');
            
            eventBus.emit(EVENTS.ROUTE_OPTIMIZED, {
                pickupCount: pickups.length,
                optimizationTime: performanceMonitor.getRecentData(1)[0]?.duration,
                savings: optimizedRoute.metrics.savings
            });

            return optimizedRoute;

        } catch (error) {
            performanceMonitor.endTimer('route_optimization');
            console.error('Route optimization failed:', error);
            throw error;
        }
    }

    /**
     * Enrich pickup data with time windows, priorities, and constraints
     */
    async enrichPickupData(pickups) {
        const enriched = [];
        
        for (const pickup of pickups) {
            const enrichedPickup = {
                ...pickup,
                timeWindow: this.calculateTimeWindow(pickup),
                priority: this.calculatePriority(pickup),
                serviceTime: this.estimateServiceTime(pickup),
                weight: pickup.estimated_weight || 5, // kg
                coordinates: {
                    lat: parseFloat(pickup.pickup_latitude),
                    lng: parseFloat(pickup.pickup_longitude)
                }
            };
            
            enriched.push(enrichedPickup);
        }
        
        return enriched;
    }

    /**
     * Calculate time window for pickup
     */
    calculateTimeWindow(pickup) {
        const now = new Date();
        const preferredTime = pickup.preferred_pickup_time ? 
            new Date(pickup.preferred_pickup_time) : 
            new Date(now.getTime() + 2 * 60 * 60 * 1000); // Default: 2 hours from now
        
        return {
            earliest: new Date(preferredTime.getTime() - 30 * 60 * 1000), // 30 min before
            latest: new Date(preferredTime.getTime() + 60 * 60 * 1000),   // 1 hour after
            preferred: preferredTime
        };
    }

    /**
     * Calculate pickup priority
     */
    calculatePriority(pickup) {
        let priority = 1.0;
        
        // Premium customers get higher priority
        if (pickup.customer?.tier === 'premium') priority += 0.5;
        
        // Urgent pickups
        if (pickup.is_urgent) priority += 0.3;
        
        // Time-sensitive pickups
        const hoursUntilPreferred = (new Date(pickup.preferred_pickup_time) - new Date()) / (1000 * 60 * 60);
        if (hoursUntilPreferred < 2) priority += 0.2;
        
        // Large orders
        if (pickup.estimated_weight > 20) priority += 0.1;
        
        return Math.min(priority, 2.0); // Cap at 2.0
    }

    /**
     * Estimate service time at pickup location
     */
    estimateServiceTime(pickup) {
        let baseTime = 5 * 60; // 5 minutes base
        
        // Add time based on order size
        const weight = pickup.estimated_weight || 5;
        baseTime += Math.floor(weight / 5) * 60; // 1 minute per 5kg
        
        // Add time for special instructions
        if (pickup.special_instructions) baseTime += 2 * 60;
        
        // Add time for apartment buildings
        if (pickup.pickup_address?.includes('apartment') || 
            pickup.pickup_address?.includes('floor')) {
            baseTime += 3 * 60;
        }
        
        return baseTime; // in seconds
    }

    /**
     * Simple optimization for small routes (≤3 stops)
     */
    async simpleOptimization(pickups, vehicle, options) {
        const branch = appState.getNearestBranch(
            pickups[0].coordinates.lat, 
            pickups[0].coordinates.lng
        );
        
        // For small routes, try all permutations
        const permutations = this.generatePermutations(pickups);
        let bestRoute = null;
        let bestScore = Infinity;
        
        for (const permutation of permutations) {
            const route = await this.calculateRouteDetails(branch, permutation, vehicle);
            const score = this.calculateRouteScore(route, options);
            
            if (score < bestScore) {
                bestScore = score;
                bestRoute = route;
            }
        }
        
        return bestRoute;
    }

    /**
     * Genetic algorithm optimization for medium routes (4-10 stops)
     */
    async geneticAlgorithmOptimization(pickups, vehicle, options) {
        const populationSize = 50;
        const generations = 100;
        const mutationRate = 0.1;
        const eliteSize = 5;
        
        const branch = appState.getNearestBranch(
            pickups[0].coordinates.lat, 
            pickups[0].coordinates.lng
        );
        
        // Initialize population
        let population = [];
        for (let i = 0; i < populationSize; i++) {
            const individual = [...pickups].sort(() => Math.random() - 0.5);
            population.push(individual);
        }
        
        // Evolution loop
        for (let gen = 0; gen < generations; gen++) {
            // Evaluate fitness
            const fitness = await Promise.all(
                population.map(async (individual) => {
                    const route = await this.calculateRouteDetails(branch, individual, vehicle);
                    return {
                        individual,
                        score: this.calculateRouteScore(route, options),
                        route
                    };
                })
            );
            
            // Sort by fitness
            fitness.sort((a, b) => a.score - b.score);
            
            // Keep elite
            const newPopulation = fitness.slice(0, eliteSize).map(f => f.individual);
            
            // Generate offspring
            while (newPopulation.length < populationSize) {
                const parent1 = this.tournamentSelection(fitness);
                const parent2 = this.tournamentSelection(fitness);
                const offspring = this.crossover(parent1, parent2);
                
                if (Math.random() < mutationRate) {
                    this.mutate(offspring);
                }
                
                newPopulation.push(offspring);
            }
            
            population = newPopulation;
        }
        
        // Return best solution
        const finalFitness = await Promise.all(
            population.map(async (individual) => {
                const route = await this.calculateRouteDetails(branch, individual, vehicle);
                return {
                    individual,
                    score: this.calculateRouteScore(route, options),
                    route
                };
            })
        );
        
        finalFitness.sort((a, b) => a.score - b.score);
        return finalFitness[0].route;
    }

    /**
     * Hybrid optimization for large routes (>10 stops)
     */
    async hybridOptimization(pickups, vehicle, options) {
        // Use clustering + local optimization
        const clusters = this.clusterPickups(pickups, 3);
        const optimizedClusters = [];
        
        for (const cluster of clusters) {
            if (cluster.length <= 3) {
                const optimized = await this.simpleOptimization(cluster, vehicle, options);
                optimizedClusters.push(optimized);
            } else {
                const optimized = await this.geneticAlgorithmOptimization(cluster, vehicle, options);
                optimizedClusters.push(optimized);
            }
        }
        
        // Optimize cluster order
        const clusterOrder = await this.optimizeClusterOrder(optimizedClusters, vehicle);
        
        // Merge into single route
        return this.mergeClusters(clusterOrder);
    }

    /**
     * Apply traffic adjustments to route
     */
    async applyTrafficAdjustments(route) {
        const adjustedRoute = { ...route };
        let totalDelay = 0;
        
        for (let i = 0; i < route.stops.length - 1; i++) {
            const from = route.stops[i];
            const to = route.stops[i + 1];
            
            // Get traffic data for this segment
            const trafficDelay = await this.getTrafficDelay(from.coordinates, to.coordinates);
            
            if (trafficDelay > 0) {
                totalDelay += trafficDelay;
                
                // Adjust arrival times
                for (let j = i + 1; j < route.stops.length; j++) {
                    route.stops[j].estimatedArrival = new Date(
                        route.stops[j].estimatedArrival.getTime() + trafficDelay * 1000
                    );
                }
            }
        }
        
        adjustedRoute.trafficDelay = totalDelay;
        adjustedRoute.totalDuration += totalDelay;
        
        return adjustedRoute;
    }

    /**
     * Calculate comprehensive route metrics
     */
    async calculateRouteMetrics(route, vehicle) {
        const metrics = {
            totalDistance: route.totalDistance || 0,
            totalDuration: route.totalDuration || 0,
            totalStops: route.stops.length,
            fuelCost: 0,
            operatingCost: 0,
            timeWindowViolations: 0,
            capacityUtilization: 0,
            savings: {},
            efficiency: {}
        };
        
        // Calculate costs
        metrics.fuelCost = (metrics.totalDistance / 1000) / vehicle.fuelEfficiency * 1.5; // Assume $1.5/liter
        metrics.operatingCost = (metrics.totalDistance / 1000) * vehicle.operatingCost;
        
        // Check time window violations
        route.stops.forEach(stop => {
            if (stop.estimatedArrival < stop.timeWindow.earliest ||
                stop.estimatedArrival > stop.timeWindow.latest) {
                metrics.timeWindowViolations++;
            }
        });
        
        // Calculate capacity utilization
        const totalWeight = route.stops.reduce((sum, stop) => sum + (stop.weight || 0), 0);
        metrics.capacityUtilization = totalWeight / vehicle.capacity;
        
        // Calculate savings compared to individual trips
        const individualTripsDistance = await this.calculateIndividualTripsDistance(route.stops);
        metrics.savings.distance = individualTripsDistance - metrics.totalDistance;
        metrics.savings.percentage = (metrics.savings.distance / individualTripsDistance) * 100;
        
        // Calculate efficiency metrics
        metrics.efficiency.stopsPerHour = metrics.totalStops / (metrics.totalDuration / 3600);
        metrics.efficiency.distancePerStop = metrics.totalDistance / metrics.totalStops;
        
        return metrics;
    }

    /**
     * Generate all permutations for small arrays
     */
    generatePermutations(arr) {
        if (arr.length <= 1) return [arr];
        
        const result = [];
        for (let i = 0; i < arr.length; i++) {
            const rest = arr.slice(0, i).concat(arr.slice(i + 1));
            const perms = this.generatePermutations(rest);
            
            for (const perm of perms) {
                result.push([arr[i]].concat(perm));
            }
        }
        
        return result;
    }

    /**
     * Tournament selection for genetic algorithm
     */
    tournamentSelection(fitness, tournamentSize = 3) {
        const tournament = [];
        for (let i = 0; i < tournamentSize; i++) {
            const randomIndex = Math.floor(Math.random() * fitness.length);
            tournament.push(fitness[randomIndex]);
        }
        
        tournament.sort((a, b) => a.score - b.score);
        return tournament[0].individual;
    }

    /**
     * Crossover operation for genetic algorithm
     */
    crossover(parent1, parent2) {
        const start = Math.floor(Math.random() * parent1.length);
        const end = Math.floor(Math.random() * (parent1.length - start)) + start;
        
        const offspring = new Array(parent1.length);
        
        // Copy segment from parent1
        for (let i = start; i <= end; i++) {
            offspring[i] = parent1[i];
        }
        
        // Fill remaining positions from parent2
        let parent2Index = 0;
        for (let i = 0; i < offspring.length; i++) {
            if (offspring[i] === undefined) {
                while (offspring.includes(parent2[parent2Index])) {
                    parent2Index++;
                }
                offspring[i] = parent2[parent2Index];
                parent2Index++;
            }
        }
        
        return offspring;
    }

    /**
     * Mutation operation for genetic algorithm
     */
    mutate(individual) {
        const i = Math.floor(Math.random() * individual.length);
        const j = Math.floor(Math.random() * individual.length);
        
        // Swap two random positions
        [individual[i], individual[j]] = [individual[j], individual[i]];
    }

    /**
     * Calculate route score for optimization
     */
    calculateRouteScore(route, options) {
        let score = 0;
        
        // Distance weight
        score += route.totalDistance * (options.prioritizeTime ? 0.3 : 0.7);
        
        // Time weight
        score += route.totalDuration * (options.prioritizeTime ? 0.7 : 0.3);
        
        // Time window violations penalty
        score += route.timeWindowViolations * 3600; // Heavy penalty
        
        // Priority bonus (negative score for high priority)
        const priorityBonus = route.stops.reduce((sum, stop) => sum + stop.priority, 0);
        score -= priorityBonus * 300;
        
        return score;
    }

    /**
     * Generate cache key for optimization results
     */
    generateCacheKey(pickups, options) {
        const pickupIds = pickups.map(p => p.id).sort().join(',');
        const optionsStr = JSON.stringify(options);
        return `${pickupIds}_${btoa(optionsStr)}`;
    }

    /**
     * Get traffic delay between two points
     */
    async getTrafficDelay(from, to) {
        // Simplified traffic delay calculation
        // In production, this would use real traffic APIs
        const distance = this.calculateDistance(from, to);
        const baseTime = distance / 40; // 40 km/h average speed
        
        // Apply traffic multiplier based on time of day
        const hour = new Date().getHours();
        let trafficMultiplier = 1.0;
        
        if ((hour >= 7 && hour <= 9) || (hour >= 17 && hour <= 19)) {
            trafficMultiplier = 1.5; // Rush hour
        } else if (hour >= 10 && hour <= 16) {
            trafficMultiplier = 1.2; // Moderate traffic
        }
        
        const adjustedTime = baseTime * trafficMultiplier;
        return Math.max(0, adjustedTime - baseTime) * 3600; // Return delay in seconds
    }

    /**
     * Calculate route details for optimization
     */
    async calculateRouteDetails(branch, pickups, vehicle) {
        const stops = [];
        let totalDistance = 0;
        let totalDuration = 0;
        let currentTime = new Date();
        
        // Start from branch
        let currentPos = {
            lat: parseFloat(branch.latitude),
            lng: parseFloat(branch.longitude)
        };
        
        // Build coordinates array for OSRM
        const coordinates = [currentPos];
        
        // Calculate route through all pickups
        for (const pickup of pickups) {
            const nextPos = pickup.coordinates;
            coordinates.push(nextPos);
            const distance = this.calculateDistance(currentPos, nextPos);
            const duration = (distance / vehicle.avgSpeed) * 3600; // seconds
            
            totalDistance += distance;
            totalDuration += duration;
            currentTime = new Date(currentTime.getTime() + duration * 1000);
            
            stops.push({
                ...pickup,
                estimatedArrival: new Date(currentTime),
                distanceFromPrevious: distance,
                durationFromPrevious: duration
            });
            
            // Add service time
            currentTime = new Date(currentTime.getTime() + pickup.serviceTime * 1000);
            totalDuration += pickup.serviceTime;
            
            currentPos = nextPos;
        }
        
        // Add return trip to branch
        const returnDistance = this.calculateDistance(currentPos, {
            lat: parseFloat(branch.latitude),
            lng: parseFloat(branch.longitude)
        });
        const returnDuration = (returnDistance / vehicle.avgSpeed) * 3600;
        totalDistance += returnDistance;
        totalDuration += returnDuration;
        
        // Add branch as final stop for complete route
        coordinates.push({
            lat: parseFloat(branch.latitude),
            lng: parseFloat(branch.longitude)
        });
        
        // Fetch actual route geometry from OSRM
        let geometry = null;
        try {
            const coordString = coordinates.map(c => `${c.lng},${c.lat}`).join(';');
            const response = await fetch(`https://router.project-osrm.org/route/v1/driving/${coordString}?overview=full&geometries=polyline`);
            const data = await response.json();
            if (data.code === 'Ok' && data.routes && data.routes[0]) {
                geometry = data.routes[0].geometry;
                // Use OSRM's more accurate distance and duration
                totalDistance = data.routes[0].distance / 1000; // Convert to km
                totalDuration = data.routes[0].duration; // Already in seconds
            }
        } catch (error) {
            console.warn('Failed to fetch OSRM geometry:', error);
        }
        
        return {
            branch,
            stops,
            totalDistance,
            totalDuration,
            vehicle,
            timeWindowViolations: 0,
            geometry,
            coordinates,
            returnToBase: true
        };
    }

    /**
     * Cluster pickups for large routes
     */
    clusterPickups(pickups, numClusters) {
        // Simple k-means clustering
        const clusters = Array(numClusters).fill(null).map(() => []);
        
        // Initialize cluster centers randomly
        const centers = [];
        for (let i = 0; i < numClusters; i++) {
            const randomPickup = pickups[Math.floor(Math.random() * pickups.length)];
            centers.push({ ...randomPickup.coordinates });
        }
        
        // Assign pickups to nearest cluster
        pickups.forEach(pickup => {
            let minDist = Infinity;
            let clusterIndex = 0;
            
            centers.forEach((center, i) => {
                const dist = this.calculateDistance(pickup.coordinates, center);
                if (dist < minDist) {
                    minDist = dist;
                    clusterIndex = i;
                }
            });
            
            clusters[clusterIndex].push(pickup);
        });
        
        return clusters.filter(c => c.length > 0);
    }

    /**
     * Optimize cluster order
     */
    async optimizeClusterOrder(clusters, vehicle) {
        // For now, return clusters as-is
        // In production, this would optimize the order of clusters
        return clusters;
    }

    /**
     * Merge clusters into single route
     */
    mergeClusters(clusters) {
        const mergedStops = [];
        let totalDistance = 0;
        let totalDuration = 0;
        
        clusters.forEach(cluster => {
            if (cluster.stops) {
                mergedStops.push(...cluster.stops);
                totalDistance += cluster.totalDistance || 0;
                totalDuration += cluster.totalDuration || 0;
            }
        });
        
        return {
            stops: mergedStops,
            totalDistance,
            totalDuration,
            timeWindowViolations: 0
        };
    }

    /**
     * Calculate individual trips distance for comparison
     */
    async calculateIndividualTripsDistance(stops) {
        const branch = appState.getNearestBranch(
            stops[0].coordinates.lat,
            stops[0].coordinates.lng
        );
        
        const branchPos = {
            lat: parseFloat(branch.latitude),
            lng: parseFloat(branch.longitude)
        };
        
        let totalDistance = 0;
        
        stops.forEach(stop => {
            // Round trip for each stop
            const distance = this.calculateDistance(branchPos, stop.coordinates);
            totalDistance += distance * 2; // Round trip
        });
        
        return totalDistance;
    }

    /**
     * Calculate distance between two coordinates
     */
    calculateDistance(pos1, pos2) {
        const R = 6371; // Earth's radius in km
        const dLat = (pos2.lat - pos1.lat) * Math.PI / 180;
        const dLon = (pos2.lng - pos1.lng) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(pos1.lat * Math.PI / 180) * Math.cos(pos2.lat * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    /**
     * Get traffic delay between two points
     */
    async getTrafficDelay(from, to) {
        // Simplified traffic delay calculation
        // In production, this would use real traffic APIs
        const distance = this.calculateDistance(from, to);
        const baseTime = distance / 40; // 40 km/h average speed
        
        // Apply traffic multiplier based on time of day
        const hour = new Date().getHours();
        let trafficMultiplier = 1.0;
        
        if ((hour >= 7 && hour <= 9) || (hour >= 17 && hour <= 19)) {
            trafficMultiplier = 1.5; // Rush hour
        } else if (hour >= 10 && hour <= 16) {
            trafficMultiplier = 1.2; // Moderate traffic
        }
        
        const adjustedTime = baseTime * trafficMultiplier;
        return Math.max(0, adjustedTime - baseTime) * 3600; // Return delay in seconds
    }
}

// Create singleton instance
export const routeOptimizer = new RouteOptimizer();

// Make it globally available
window.routeOptimizer = routeOptimizer;