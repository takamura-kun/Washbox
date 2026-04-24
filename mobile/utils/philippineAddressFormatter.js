// Address formatting utility for Philippine addresses
export class PhilippineAddressFormatter {
  
  // Common street name variations in Dumaguete
  static DUMAGUETE_STREET_ALIASES = {
    'Dr. V Locsin': 'Dr. Vicente Locsin Street',
    'Dr V Locsin': 'Dr. Vicente Locsin Street',
    'V Locsin': 'Dr. Vicente Locsin Street',
    'Rizal Blvd': 'Rizal Boulevard',
    'Colon': 'Colon Street',
    'Silliman Ave': 'Silliman Avenue',
    'Perdices': 'Perdices Street',
    'San Juan': 'San Juan Street',
    'Real': 'Real Street',
    'Hibbard': 'Hibbard Avenue'
  };

  // Barangay mappings for Dumaguete City
  static DUMAGUETE_BARANGAYS = [
    'Barangay I (Poblacion)', 'Barangay II (Poblacion)', 'Barangay III (Poblacion)',
    'Barangay IV (Poblacion)', 'Barangay V (Poblacion)', 'Barangay VI (Poblacion)',
    'Barangay VII (Poblacion)', 'Barangay VIII (Poblacion)', 'Bantayan', 'Batinguel',
    'Bunao', 'Calindagan', 'Camanjac', 'Candau-ay', 'Cantil-e', 'Daro', 'Junob',
    'Looc', 'Mangnao', 'Motong', 'Piapi', 'Pulantubig', 'Tabuc-tubig', 'Taclobo',
    'Talay', 'Tinago', 'Tubtubon'
  ];

  /**
   * Format Philippine address for better geocoding
   */
  static formatForGeocoding(address) {
    if (!address || typeof address !== 'string') return address;

    let formatted = address.trim();

    // Normalize street names
    Object.entries(this.DUMAGUETE_STREET_ALIASES).forEach(([alias, full]) => {
      const regex = new RegExp(`\\b${alias.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\b`, 'gi');
      formatted = formatted.replace(regex, full);
    });

    // Add missing components for Dumaguete addresses
    if (formatted.toLowerCase().includes('dumaguete')) {
      // Add province if missing
      if (!formatted.toLowerCase().includes('negros oriental')) {
        formatted = formatted.replace(/dumaguete\s*city/gi, 'Dumaguete City, Negros Oriental');
      }
      
      // Add country if missing
      if (!formatted.toLowerCase().includes('philippines')) {
        formatted += ', Philippines';
      }

      // Add postal code if missing (Dumaguete is 6200)
      if (!formatted.match(/\b6200\b/)) {
        formatted = formatted.replace(/Dumaguete City/gi, 'Dumaguete City 6200');
      }
    }

    return formatted;
  }

  /**
   * Get fallback coordinates for known Dumaguete locations
   */
  static getFallbackCoordinates(address) {
    const addr = address.toLowerCase();
    
    // Dumaguete City center coordinates
    const DUMAGUETE_CENTER = { latitude: 9.3068, longitude: 123.3054 };
    
    // Known location coordinates in Dumaguete
    const KNOWN_LOCATIONS = {
      'rizal boulevard': { latitude: 9.3089, longitude: 123.3065 },
      'silliman university': { latitude: 9.3103, longitude: 123.3017 },
      'robinsons dumaguete': { latitude: 9.3034, longitude: 123.3028 },
      'lee plaza': { latitude: 9.3076, longitude: 123.3042 },
      'dumaguete airport': { latitude: 9.3337, longitude: 123.3006 },
      'public market': { latitude: 9.3081, longitude: 123.3058 },
      'city hall': { latitude: 9.3075, longitude: 123.3048 }
    };

    // Check for known landmarks
    for (const [landmark, coords] of Object.entries(KNOWN_LOCATIONS)) {
      if (addr.includes(landmark)) {
        return coords;
      }
    }

    // Check for street names and provide approximate coordinates
    if (addr.includes('dr. vicente locsin') || addr.includes('dr v locsin')) {
      return { latitude: 9.3065, longitude: 123.3045 }; // Dr. V Locsin Street area
    }
    
    if (addr.includes('colon')) {
      return { latitude: 9.3078, longitude: 123.3052 }; // Colon Street area
    }

    // Default to Dumaguete city center
    if (addr.includes('dumaguete')) {
      return DUMAGUETE_CENTER;
    }

    return null;
  }

  /**
   * Validate if coordinates are within reasonable bounds for Philippines
   */
  static isValidPhilippineCoordinates(lat, lng) {
    // Philippines bounds: roughly 4°N to 21°N, 116°E to 127°E
    return lat >= 4 && lat <= 21 && lng >= 116 && lng <= 127;
  }

  /**
   * Enhanced geocoding with fallbacks
   */
  static async geocodeAddress(address) {
    try {
      // First, try with formatted address
      const formattedAddress = this.formatForGeocoding(address);
      console.log('Formatted address:', formattedAddress);

      // Try geocoding with formatted address
      // (This would use your existing geocoding service)
      const result = await this.performGeocoding(formattedAddress);
      
      if (result && this.isValidPhilippineCoordinates(result.latitude, result.longitude)) {
        return result;
      }

      // If geocoding fails or returns invalid coordinates, use fallback
      console.log('Geocoding failed or invalid coordinates, using fallback');
      const fallback = this.getFallbackCoordinates(address);
      
      if (fallback) {
        return {
          ...fallback,
          address: formattedAddress,
          source: 'fallback'
        };
      }

      throw new Error('Could not determine coordinates for address');
      
    } catch (error) {
      console.error('Geocoding error:', error);
      
      // Last resort: return Dumaguete center
      return {
        latitude: 9.3068,
        longitude: 123.3054,
        address: address,
        source: 'default'
      };
    }
  }

  /**
   * Placeholder for actual geocoding service call
   */
  static async performGeocoding(address) {
    // This would call your actual geocoding service
    // For now, return null to trigger fallback
    return null;
  }
}

export default PhilippineAddressFormatter;