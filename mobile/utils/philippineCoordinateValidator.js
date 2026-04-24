// Coordinate validation for Philippine addresses
export class PhilippineCoordinateValidator {
  
  // Known coordinates for Dumaguete City landmarks
  static DUMAGUETE_LANDMARKS = {
    'dr_v_locsin_street': { latitude: 9.3065, longitude: 123.3045 },
    'rizal_boulevard': { latitude: 9.3089, longitude: 123.3065 },
    'silliman_university': { latitude: 9.3103, longitude: 123.3017 },
    'robinsons_dumaguete': { latitude: 9.3034, longitude: 123.3028 },
    'lee_plaza': { latitude: 9.3076, longitude: 123.3042 },
    'dumaguete_airport': { latitude: 9.3337, longitude: 123.3006 },
    'public_market': { latitude: 9.3081, longitude: 123.3058 },
    'city_hall': { latitude: 9.3075, longitude: 123.3048 },
    'dumaguete_center': { latitude: 9.3068, longitude: 123.3054 }
  };

  // Dumaguete City bounds
  static DUMAGUETE_BOUNDS = {
    north: 9.35,
    south: 9.25,
    east: 123.35,
    west: 123.25
  };

  // Philippines bounds
  static PHILIPPINES_BOUNDS = {
    north: 21.0,
    south: 4.0,
    east: 127.0,
    west: 116.0
  };

  /**
   * Check if coordinates are within Philippines
   */
  static isInPhilippines(latitude, longitude) {
    return latitude >= this.PHILIPPINES_BOUNDS.south &&
           latitude <= this.PHILIPPINES_BOUNDS.north &&
           longitude >= this.PHILIPPINES_BOUNDS.west &&
           longitude <= this.PHILIPPINES_BOUNDS.east;
  }

  /**
   * Check if coordinates are within Dumaguete City
   */
  static isInDumaguete(latitude, longitude) {
    return latitude >= this.DUMAGUETE_BOUNDS.south &&
           latitude <= this.DUMAGUETE_BOUNDS.north &&
           longitude >= this.DUMAGUETE_BOUNDS.west &&
           longitude <= this.DUMAGUETE_BOUNDS.east;
  }

  /**
   * Get fallback coordinates for Dumaguete addresses
   */
  static getDumagueteFallback(address) {
    const addr = address.toLowerCase();
    
    // Check for specific landmarks
    if (addr.includes('dr. v locsin') || addr.includes('dr v locsin') || addr.includes('locsin')) {
      return this.DUMAGUETE_LANDMARKS.dr_v_locsin_street;
    }
    
    if (addr.includes('rizal boulevard') || addr.includes('rizal blvd')) {
      return this.DUMAGUETE_LANDMARKS.rizal_boulevard;
    }
    
    if (addr.includes('silliman')) {
      return this.DUMAGUETE_LANDMARKS.silliman_university;
    }
    
    if (addr.includes('robinsons')) {
      return this.DUMAGUETE_LANDMARKS.robinsons_dumaguete;
    }
    
    if (addr.includes('lee plaza')) {
      return this.DUMAGUETE_LANDMARKS.lee_plaza;
    }
    
    // Default to city center
    return this.DUMAGUETE_LANDMARKS.dumaguete_center;
  }

  /**
   * Validate and correct coordinates for Philippine addresses
   */
  static validateAndCorrect(address, latitude, longitude) {
    // If coordinates are not in Philippines, use fallback
    if (!this.isInPhilippines(latitude, longitude)) {
      console.log(`Coordinates ${latitude}, ${longitude} not in Philippines. Using fallback for: ${address}`);
      
      if (address.toLowerCase().includes('dumaguete')) {
        return {
          ...this.getDumagueteFallback(address),
          corrected: true,
          reason: 'outside_philippines'
        };
      }
      
      // Default to Philippines center (somewhere in Luzon)
      return {
        latitude: 14.5995,
        longitude: 120.9842,
        corrected: true,
        reason: 'default_philippines'
      };
    }

    // If it's a Dumaguete address but coordinates are not in Dumaguete
    if (address.toLowerCase().includes('dumaguete') && !this.isInDumaguete(latitude, longitude)) {
      console.log(`Dumaguete address but coordinates ${latitude}, ${longitude} not in Dumaguete. Using fallback.`);
      
      return {
        ...this.getDumagueteFallback(address),
        corrected: true,
        reason: 'outside_dumaguete'
      };
    }

    // Coordinates are valid
    return {
      latitude,
      longitude,
      corrected: false
    };
  }

  /**
   * Format address for better geocoding accuracy
   */
  static formatAddress(address) {
    if (!address) return address;

    let formatted = address.trim();

    // Expand common abbreviations
    formatted = formatted
      .replace(/\bDr\.?\s+V\.?\s+Locsin\b/gi, 'Doctor Vicente Locsin Street')
      .replace(/\bDr\.?\s+Locsin\b/gi, 'Doctor Vicente Locsin Street')
      .replace(/\bRizal\s+Blvd\b/gi, 'Rizal Boulevard')
      .replace(/\bSt\.?\b/gi, 'Street')
      .replace(/\bAve\.?\b/gi, 'Avenue')
      .replace(/\bBlvd\.?\b/gi, 'Boulevard');

    // Ensure Dumaguete addresses have full location info
    if (formatted.toLowerCase().includes('dumaguete')) {
      // Add province if missing
      if (!formatted.toLowerCase().includes('negros oriental')) {
        formatted = formatted.replace(/dumaguete\s*city/gi, 'Dumaguete City, Negros Oriental');
      }
      
      // Add country if missing
      if (!formatted.toLowerCase().includes('philippines')) {
        formatted += ', Philippines';
      }
    }

    return formatted;
  }
}

export default PhilippineCoordinateValidator;