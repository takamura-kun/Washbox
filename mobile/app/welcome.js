import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  Image,
  Dimensions,
  Animated,
  Platform,
  StatusBar,
  FlatList,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { router } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import { API_BASE_URL } from '../constants/config';
import PromotionModal from '../components/PromotionModal';

const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');
const CARD_WIDTH = SCREEN_WIDTH - 48;

// ─── Design Tokens ────────────────────────────────────────────────────────────
const C = {
  bg:           '#05071A',
  surface:      '#0C1030',
  surfaceUp:    '#141A40',
  border:       'rgba(255,255,255,0.07)',
  borderUp:     'rgba(255,255,255,0.13)',

  sky:          '#38BDF8',
  skyDim:       '#0EA5E9',
  skyGlow:      'rgba(56, 189, 248, 0.18)',
  skyDeep:      'rgba(56, 189, 248, 0.08)',

  violet:       '#A78BFA',
  emerald:      '#34D399',
  amber:        '#FBBF24',
  rose:         '#FB7185',

  white:        '#F8FAFC',
  text:         '#CBD5E1',
  muted:        '#64748B',
  faint:        '#334155',
};

const HERO_GRADIENT   = ['#1a3a6e', '#0c1e4a', '#05071A'];
const SKY_GRADIENT    = [C.skyDim, '#3B82F6', '#6366F1'];
const CARD_GRADIENTS  = [
  ['rgba(56,189,248,0.13)',  'rgba(56,189,248,0.04)'],
  ['rgba(167,139,250,0.13)', 'rgba(167,139,250,0.04)'],
  ['rgba(52,211,153,0.13)',  'rgba(52,211,153,0.04)'],
  ['rgba(251,191,36,0.13)',  'rgba(251,191,36,0.04)'],
];
const CARD_ACCENTS = [C.sky, C.violet, C.emerald, C.amber];

// ─── Helpers ──────────────────────────────────────────────────────────────────
const hex2rgba = (hex, a) => {
  const r = parseInt(hex.slice(1,3),16);
  const g = parseInt(hex.slice(3,5),16);
  const b = parseInt(hex.slice(5,7),16);
  return `rgba(${r},${g},${b},${a})`;
};

const getServiceIcon = (name = '') => {
  const n = name.toLowerCase();
  if (n.includes('drop') || n.includes('off'))       return 'bag-check-outline';
  if (n.includes('self') || n.includes('service'))   return 'reload-circle-outline';
  if (n.includes('pickup') || n.includes('delivery'))return 'car-outline';
  if (n.includes('wash') && n.includes('fold'))      return 'shirt-outline';
  if (n.includes('dry') && n.includes('clean'))      return 'sparkles-outline';
  if (n.includes('iron') || n.includes('press'))     return 'flame-outline';
  return 'water-outline';
};

const formatPrice = (s) => {
  if (s?.price_per_kilo)  return `₱${parseFloat(s.price_per_kilo).toFixed(2)} / kg`;
  if (s?.price_per_load)  return `₱${parseFloat(s.price_per_load).toFixed(2)} / load`;
  return 'Contact for pricing';
};

const StarRow = ({ rating, size = 15 }) => (
  <View style={{ flexDirection: 'row', gap: 3 }}>
    {[1,2,3,4,5].map(i => (
      <Ionicons
        key={i}
        name={i <= rating ? 'star' : 'star-outline'}
        size={size}
        color={i <= rating ? C.amber : C.faint}
      />
    ))}
  </View>
);

// ─── Dot Indicator ────────────────────────────────────────────────────────────
const Dots = ({ count, active, color = C.sky }) => (
  <View style={{ flexDirection:'row', gap:6, justifyContent:'center', alignItems:'center' }}>
    {Array.from({ length: count }).map((_,i) => (
      <View key={i} style={{
        height: 6,
        width: i === active ? 22 : 6,
        borderRadius: 3,
        backgroundColor: i === active ? color : C.faint,
      }} />
    ))}
  </View>
);

// ─── Section Header ───────────────────────────────────────────────────────────
const SectionHead = ({ eyebrow, title, subtitle, accentColor = C.sky }) => (
  <View style={{ alignItems:'center', marginBottom: 28 }}>
    <View style={[styles.eyebrowPill, { borderColor: hex2rgba(accentColor, 0.35), backgroundColor: hex2rgba(accentColor, 0.09) }]}>
      <View style={[styles.eyebrowDot, { backgroundColor: accentColor }]} />
      <Text style={[styles.eyebrow, { color: accentColor }]}>{eyebrow}</Text>
    </View>
    <Text style={styles.sectionTitle}>{title}</Text>
    {subtitle && <Text style={styles.sectionSub}>{subtitle}</Text>}
  </View>
);

// ─── Main Component ───────────────────────────────────────────────────────────
export default function WelcomeScreen() {
  const [branches,    setBranches]    = useState([]);
  const [feedbacks,   setFeedbacks]   = useState([]);
  const [services,    setServices]    = useState([]);
  const [promotions,  setPromotions]  = useState([]);
  const [showPromo,   setShowPromo]   = useState(false);
  const [activePromo, setActivePromo] = useState(null);
  const [promoIndex,  setPromoIndex]  = useState(0);

  const [heroSlide,   setHeroSlide]   = useState(0);
  const [activeFeed,  setActiveFeed]  = useState(0);
  const [activeSvc,   setActiveSvc]   = useState(0);
  const [activeBranch,setActiveBranch]= useState(0);
  const [activeMachine,setActiveMachine]= useState(0);

  // Entrance animations
  const fadeAnim  = useRef(new Animated.Value(0)).current;
  const riseAnim  = useRef(new Animated.Value(40)).current;
  const pulseAnim = useRef(new Animated.Value(1)).current;

  // Carousel scroll refs
  const svcRef    = useRef(null);
  const feedRef   = useRef(null);
  const branchRef = useRef(null);
  const machineRef = useRef(null);

  const carouselImages = [
    require('../assets/images/washbox.jpg'),
    require('../assets/images/washbox1.jpg'),
    require('../assets/images/washbox2.jpg'),
    require('../assets/images/washbox3.jpg'),
    require('../assets/images/washbox4.jpg'),
    require('../assets/images/washbox5.jpg'),
    require('../assets/images/washbox6.jpg'),
    require('../assets/images/washbox7.jpg'),
  ];

  // ── Entrance ──
  useEffect(() => {
    Animated.parallel([
      Animated.timing(fadeAnim,  { toValue:1, duration:700, useNativeDriver:true }),
      Animated.spring(riseAnim,  { toValue:0, tension:55, friction:11, useNativeDriver:true }),
    ]).start();

    // Subtle CTA pulse
    Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, { toValue:1.04, duration:1600, useNativeDriver:true }),
        Animated.timing(pulseAnim, { toValue:1,    duration:1600, useNativeDriver:true }),
      ])
    ).start();
  }, []);

  // ── Auto-advance: hero ──
  useEffect(() => {
    const t = setInterval(() => setHeroSlide(p => (p+1) % carouselImages.length), 4000);
    return () => clearInterval(t);
  }, []);

  // ── Auto-advance: services ──
  useEffect(() => {
    if (!services.length) return;
    const t = setInterval(() => {
      setActiveSvc(p => {
        const next = (p+1) % services.length;
        svcRef.current?.scrollToIndex({ index: next, animated: true });
        return next;
      });
    }, 4500);
    return () => clearInterval(t);
  }, [services.length]);

  // ── Auto-advance: machines ──
  useEffect(() => {
    const t = setInterval(() => {
      setActiveMachine(p => {
        const next = (p+1) % MACHINES.length;
        machineRef.current?.scrollToIndex({ index: next, animated: true });
        return next;
      });
    }, 5000); // 5 seconds per machine
    return () => clearInterval(t);
  }, []);

  // ── Auto-advance: feedback ──
  useEffect(() => {
    if (!feedbacks.length) return;
    const t = setInterval(() => {
      setActiveFeed(p => {
        const next = (p+1) % feedbacks.length;
        feedRef.current?.scrollToIndex({ index: next, animated: true });
        return next;
      });
    }, 5500);
    return () => clearInterval(t);
  }, [feedbacks.length]);

  // ── Fetch ──
  const JSON_HEADERS = { 'Accept': 'application/json' };

  const fetchBranches = useCallback(async () => {
    try {
      const res = await fetch(`${API_BASE_URL}/v1/branches`, { headers: JSON_HEADERS });
      const d   = await res.json();
      if (d.success) setBranches(d.data.branches || []);
    } catch {}
  }, []);

  const fetchFeedbacks = useCallback(async () => {
    try {
      const res = await fetch(`${API_BASE_URL}/v1/ratings/public?limit=10`, { headers: JSON_HEADERS });
      const d   = await res.json();
      if (d.success && d.data.ratings) setFeedbacks(d.data.ratings);
    } catch {}
  }, []);

  const fetchServices = useCallback(async () => {
    try {
      const res = await fetch(`${API_BASE_URL}/v1/services`, { headers: JSON_HEADERS });
      const d   = await res.json();
      if (d.success && d.data) setServices(d.data);
    } catch (error) {
      console.error('Failed to fetch services:', error);
    }
  }, []);

  const fetchPromotions = useCallback(async () => {
    try {
      const res = await fetch(`${API_BASE_URL}/v1/promotions/featured`, { headers: JSON_HEADERS });
      const d   = await res.json();
      if (d.success && d.data.promotions && d.data.promotions.length > 0) {
        setPromotions(d.data.promotions);
        setActivePromo(d.data.promotions[0]);
        setPromoIndex(0);
        setShowPromo(true);
      }
    } catch {}
  }, []);

  // ── Auto-cycle promotions ──
  useEffect(() => {
    if (!promotions.length) return;
    
    const timer = setInterval(() => {
      setPromoIndex(prev => {
        const next = (prev + 1) % promotions.length;
        setActivePromo(promotions[next]);
        setShowPromo(true);
        return next;
      });
    }, 60000); // Show next promotion every 1 minute
    
    return () => clearInterval(timer);
  }, [promotions.length]);

  useEffect(() => {
    fetchBranches();
    fetchFeedbacks();
    fetchServices();
    fetchPromotions();
  }, []);

  // ── FlatList viewability (sync dots) ──
  const onSvcView  = useCallback(({ viewableItems }) => {
    if (viewableItems[0]) setActiveSvc(viewableItems[0].index);
  }, []);
  const onFeedView = useCallback(({ viewableItems }) => {
    if (viewableItems[0]) setActiveFeed(viewableItems[0].index);
  }, []);
  const onBranchView = useCallback(({ viewableItems }) => {
    if (viewableItems[0]) setActiveBranch(viewableItems[0].index);
  }, []);
  const onMachineView = useCallback(({ viewableItems }) => {
    if (viewableItems[0]) setActiveMachine(viewableItems[0].index);
  }, []);

  const viewConfig = useRef({ viewAreaCoveragePercentThreshold: 55 }).current;

  // ── Render items ──
  const renderService = useCallback(({ item: svc, index }) => {
    const accent  = CARD_ACCENTS[index % CARD_ACCENTS.length];
    const [g1,g2] = CARD_GRADIENTS[index % CARD_GRADIENTS.length];
    
    // Use image_url or icon_url from the API response
    const serviceImage = svc.image_url || svc.icon_url;
    
    return (
      <TouchableOpacity
        style={styles.flatCard}
        onPress={() => router.push('/(auth)/login')}
        activeOpacity={0.88}
      >
        {/* Background Image */}
        {serviceImage && (
          <>
            <Image 
              source={{ uri: serviceImage }} 
              style={styles.svcBgImage} 
              resizeMode="cover" 
            />
            {/* Dark overlay for better text readability */}
            <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(5,7,26,0.75)' }]} />
          </>
        )}
        
        <LinearGradient colors={[g1, g2]} style={styles.svcGradient}>
          <View style={styles.svcTopRow}>
            <View style={[styles.svcIconBubble, { backgroundColor: hex2rgba(accent, 0.15), borderColor: hex2rgba(accent, 0.3) }]}>
              <Ionicons name={getServiceIcon(svc.name)} size={34} color={accent} />
            </View>
            <View style={[styles.svcArrowBtn, { borderColor: hex2rgba(accent, 0.35) }]}>
              <Ionicons name="arrow-forward" size={16} color={accent} />
            </View>
          </View>

          <Text style={styles.svcName}>{svc.name}</Text>
          <Text style={styles.svcDesc} numberOfLines={2}>
            {svc.description || 'Professional laundry service with quality guarantee'}
          </Text>

          <View style={styles.svcFooter}>
            <View style={[styles.pricePill, { backgroundColor: hex2rgba(accent, 0.14), borderColor: hex2rgba(accent, 0.3) }]}>
              <Ionicons name="pricetag-outline" size={12} color={accent} />
              <Text style={[styles.priceText, { color: accent }]}>{formatPrice(svc)}</Text>
            </View>
            {svc.turnaround_time && (
              <View style={styles.timePill}>
                <Ionicons name="time-outline" size={12} color={C.muted} />
                <Text style={styles.timeText}>{svc.turnaround_time}</Text>
              </View>
            )}
          </View>
        </LinearGradient>
      </TouchableOpacity>
    );
  }, []);

  const renderFeedback = useCallback(({ item: fb }) => (
    <View style={[styles.flatCard, styles.feedCard]}>
      <StarRow rating={fb.rating} />
      <Text style={styles.feedQuoteMark}>&quot;</Text>
      <Text style={styles.feedText}>{fb.comment}</Text>
      <View style={styles.feedAuthorRow}>
        <View style={[styles.feedAvatar, { backgroundColor: C.sky }]}>
          <Text style={styles.feedAvatarLetter}>{fb.customer?.charAt(0)?.toUpperCase()}</Text>
        </View>
        <View>
          <Text style={styles.feedName}>{fb.customer}</Text>
          <Text style={styles.feedBranch}>{fb.branch}</Text>
        </View>
      </View>
    </View>
  ), []);

  const renderBranch = useCallback(({ item: branch, index }) => {
    const accent  = CARD_ACCENTS[index % CARD_ACCENTS.length];
    const [g1,g2] = CARD_GRADIENTS[index % CARD_GRADIENTS.length];
    return (
      <View style={[styles.flatCard, styles.branchCard]}>
        <View style={[styles.branchGrad, { backgroundColor: g1 }]}>
          <View style={[styles.branchIconBubble, { backgroundColor: hex2rgba(accent, 0.14), borderColor: hex2rgba(accent, 0.28) }]}>
            <Ionicons name="location" size={28} color={accent} />
          </View>

          <Text style={styles.branchName}>{branch.name}</Text>
          <Text style={styles.branchAddr} numberOfLines={2}>{branch.address}</Text>

          <View style={styles.branchMeta}>
            {branch.average_rating ? (
              <View style={[styles.metaPill, { backgroundColor: hex2rgba(C.amber, 0.12), borderColor: hex2rgba(C.amber, 0.3) }]}>
                <Ionicons name="star" size={13} color={C.amber} />
                <Text style={[styles.metaText, { color: C.amber }]}>
                  {parseFloat(branch.average_rating).toFixed(1)}
                </Text>
                <Text style={styles.metaTextMuted}>({branch.ratings_count || 0})</Text>
              </View>
            ) : null}

            {branch.phone ? (
              <View style={[styles.metaPill, { backgroundColor: hex2rgba(accent, 0.1), borderColor: hex2rgba(accent, 0.25) }]}>
                <Ionicons name="call-outline" size={13} color={accent} />
                <Text style={[styles.metaText, { color: accent }]}>{branch.phone}</Text>
              </View>
            ) : null}

            {branch.is_open !== undefined && (
              <View style={[styles.metaPill, {
                backgroundColor: branch.is_open ? hex2rgba(C.emerald, 0.12) : hex2rgba(C.rose, 0.1),
                borderColor:     branch.is_open ? hex2rgba(C.emerald, 0.3)  : hex2rgba(C.rose, 0.25),
              }]}>
                <View style={[styles.statusDot, { backgroundColor: branch.is_open ? C.emerald : C.rose }]} />
                <Text style={[styles.metaText, { color: branch.is_open ? C.emerald : C.rose }]}>
                  {branch.is_open ? 'Open Now' : 'Closed'}
                </Text>
              </View>
            )}
          </View>
        </View>
      </View>
    );
  }, []);

  const renderMachine = useCallback(({ item: machine, index }) => {
    const accent  = CARD_ACCENTS[index % CARD_ACCENTS.length];
    
    return (
      <View style={[styles.flatCard, styles.machineCard]}>
        <Image source={machine.image} style={styles.machineBgImage} resizeMode="cover" />
        <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(5,7,26,0.8)' }]} />
        
        <View style={styles.machineGrad}>
          <View style={[styles.machineIconBubble, { backgroundColor: hex2rgba(accent, 0.15), borderColor: hex2rgba(accent, 0.3) }]}>
            <Ionicons name="hardware-chip-outline" size={32} color={accent} />
          </View>

          <Text style={styles.machineName}>{machine.name}</Text>
          <Text style={styles.machineDesc} numberOfLines={3}>{machine.description}</Text>

          <View style={[styles.capacityPill, { backgroundColor: hex2rgba(accent, 0.14), borderColor: hex2rgba(accent, 0.3) }]}>
            <Ionicons name="cube-outline" size={14} color={accent} />
            <Text style={[styles.capacityText, { color: accent }]}>{machine.capacity}</Text>
          </View>

          <View style={styles.machineFeatures}>
            {machine.features.map((feature, i) => (
              <View key={i} style={styles.featurePill}>
                <Ionicons name="checkmark-circle" size={12} color={C.emerald} />
                <Text style={styles.featureText}>{feature}</Text>
              </View>
            ))}
          </View>
        </View>
      </View>
    );
  }, []);

  // ── Machine images ──
  const machineImages = [
    require('../assets/images/machines/Lg.jpeg'),
    require('../assets/images/machines/Lg2.jpeg'),
    require('../assets/images/machines/Lg3.jpeg'),
    require('../assets/images/machines/LG4.jpg'),
  ];

  // ── Machine data ──
  const MACHINES = [
    {
      name: 'LG Commercial Washer',
      description: 'High-capacity front-loading washers with advanced cleaning technology and gentle fabric care.',
      capacity: '8-12 kg',
      features: ['Energy Efficient', 'Gentle on Fabrics', 'Multiple Wash Programs'],
      image: machineImages[0]
    },
    {
      name: 'LG TurboWash™ System',
      description: 'Revolutionary washing technology that reduces wash time while maintaining superior cleaning performance.',
      capacity: '10-15 kg',
      features: ['Fast Wash Cycles', 'Deep Clean Technology', 'Water Saving'],
      image: machineImages[1]
    },
    {
      name: 'LG Smart Inverter',
      description: 'Intelligent washing machines with inverter technology for quiet operation and energy efficiency.',
      capacity: '8-14 kg',
      features: ['Quiet Operation', 'Smart Controls', 'Durable Motor'],
      image: machineImages[2]
    },
    {
      name: 'LG Commercial Dryer',
      description: 'Professional-grade dryers with precise temperature control and fabric protection systems.',
      capacity: '8-12 kg',
      features: ['Sensor Dry', 'Wrinkle Care', 'Multiple Heat Settings'],
      image: machineImages[3]
    },
  ];

  // ── Features data ──
  const FEATURES = [
    { icon:'shield-checkmark-outline', color:C.emerald, label:'100% Satisfaction',  desc:'Rewash guarantee if not happy' },
    { icon:'time-outline',             color:C.amber,   label:'Fast Turnaround',     desc:'24-hour standard service' },
    { icon:'heart-outline',            color:C.rose,    label:'Gentle Care',         desc:'Special care for delicates' },
    { icon:'leaf-outline',             color:C.emerald, label:'Eco-Friendly',        desc:'Safe, green detergents' },
  ];

  // ─────────────────────────────────────────────────────────────────────────────
  return (
    <View style={styles.root}>
      <StatusBar barStyle="light-content" backgroundColor={C.bg} />

      <PromotionModal
        visible={showPromo}
        promotion={activePromo}
        onClose={() => setShowPromo(false)}
      />

      <ScrollView
        showsVerticalScrollIndicator={false}
        contentContainerStyle={{ paddingBottom: 60 }}
      >
        <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY: riseAnim }] }}>

          {/* ══ HERO ════════════════════════════════════════════════════════════ */}
          <LinearGradient colors={HERO_GRADIENT} style={styles.hero}>

            {/* Decorative orbs */}
            <View style={[styles.orb, { top: -60, right: -60,  backgroundColor: hex2rgba(C.sky, 0.18) }]} />
            <View style={[styles.orb, { top:  80, left: -80,   backgroundColor: hex2rgba(C.violet, 0.12), width:200, height:200 }]} />

            <View style={styles.heroInner}>
              {/* Logo + wordmark */}
              <View style={styles.wordmarkRow}>
                <View style={styles.logoBorder}>
                  <Image source={require('../assets/images/icon.png')} style={styles.logo} />
                </View>
                <View>
                  <Text style={styles.heroTitle}>WashBox</Text>
                  <Text style={styles.heroClaim}>Fresh Laundry. Zero Hassle.</Text>
                </View>
              </View>

              <Text style={styles.heroDesc}>
                Professional laundry at your fingertips — drop-off, pickup & delivery across Negros Oriental.
              </Text>

              {/* ── Hero image carousel ── */}
              <View style={styles.heroCarousel}>
                {carouselImages.map((img, i) => (
                  <Animated.View
                    key={i}
                    style={[
                      StyleSheet.absoluteFill,
                      {
                        opacity:   heroSlide === i ? 1 : 0,
                        transform: [{ scale: heroSlide === i ? 1 : 0.96 }],
                      },
                    ]}
                  >
                    <Image source={img} style={styles.heroCarouselImg} />
                    {/* subtle vignette */}
                    <LinearGradient
                      colors={['transparent', 'rgba(5,7,26,0.55)']}
                      style={StyleSheet.absoluteFill}
                    />
                  </Animated.View>
                ))}

                {/* Slide counter badge */}
                <View style={styles.slideBadge}>
                  <Text style={styles.slideBadgeText}>{heroSlide + 1} / {carouselImages.length}</Text>
                </View>
              </View>

              <Dots count={carouselImages.length} active={heroSlide} />

              {/* CTA */}
              <Animated.View style={{ transform:[{ scale: pulseAnim }], width:'100%', marginTop:28 }}>
                <TouchableOpacity onPress={() => router.push('/(auth)/login')} activeOpacity={0.85}>
                  <LinearGradient colors={SKY_GRADIENT} style={styles.heroCTA}>
                    <Text style={styles.heroCTAText}>Get Started</Text>
                    <Ionicons name="arrow-forward-circle" size={22} color="#fff" />
                  </LinearGradient>
                </TouchableOpacity>
              </Animated.View>

              <TouchableOpacity
                onPress={() => router.push('/(auth)/login')}
                style={styles.loginLink}
                activeOpacity={0.7}
              >
                <Text style={styles.loginLinkText}>Already have an account? <Text style={{ color: C.sky, fontWeight:'700' }}>Log in</Text></Text>
              </TouchableOpacity>
            </View>
          </LinearGradient>

          {/* ══ STATS BAR ═══════════════════════════════════════════════════════ */}
          <View style={styles.statsBar}>
            {[
              { value: branches.length || '3', label: 'Branches' },
              { value: feedbacks.length ? `${(feedbacks.reduce((a,f)=>a+f.rating,0)/feedbacks.length).toFixed(1)}★` : '5.0★', label: 'Avg Rating' },
              { value: services.length  || '6+', label: 'Services' },
            ].map((s, i) => (
              <View key={i} style={[styles.statItem, i < 2 && styles.statDivider]}>
                <Text style={styles.statValue}>{s.value}</Text>
                <Text style={styles.statLabel}>{s.label}</Text>
              </View>
            ))}
          </View>

          {/* ══ SERVICES ════════════════════════════════════════════════════════ */}
          <View style={styles.section}>
            <SectionHead
              eyebrow="WHAT WE OFFER"
              title="Our Services"
              subtitle="Everything your clothes need, handled with care"
              accentColor={C.sky}
            />

            {services.length > 0 ? (
              <>
                <FlatList
                  ref={svcRef}
                  data={services}
                  renderItem={renderService}
                  keyExtractor={i => String(i.id)}
                  horizontal
                  pagingEnabled
                  showsHorizontalScrollIndicator={false}
                  snapToInterval={CARD_WIDTH + 16}
                  snapToAlignment="start"
                  decelerationRate="fast"
                  contentContainerStyle={{ paddingHorizontal: 24, gap: 16 }}
                  onViewableItemsChanged={onSvcView}
                  viewabilityConfig={viewConfig}
                  getItemLayout={(_, i) => ({ length: CARD_WIDTH + 16, offset: (CARD_WIDTH + 16) * i, index: i })}
                  removeClippedSubviews={true}
                  maxToRenderPerBatch={3}
                  windowSize={5}
                  initialNumToRender={2}
                />
                <View style={{ marginTop: 18 }}>
                  <Dots count={services.length} active={activeSvc} color={C.sky} />
                </View>
              </>
            ) : (
              /* Fallback static cards */
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal:24, gap:16 }}>
                {[
                  { name:'Drop-Off Service', desc:'Drop your laundry and pick it up fresh & folded', price:'From ₱79 / load', icon:'bag-check-outline', accent:C.sky },
                  { name:'Self-Service',     desc:'Use our modern machines at your own pace',         price:'From ₱50 / cycle', icon:'reload-circle-outline', accent:C.violet },
                  { name:'Pickup & Delivery',desc:'We come to you — schedule a pickup easily',         price:'Free pickup • Bais City', icon:'car-outline', accent:C.emerald },
                ].map((s, i) => (
                  <TouchableOpacity key={i} style={[styles.flatCard, { width: CARD_WIDTH * 0.78 }]} onPress={() => router.push('/(auth)/login')} activeOpacity={0.88}>
                    <LinearGradient colors={CARD_GRADIENTS[i]} style={styles.svcGradient}>
                      <View style={[styles.svcIconBubble, { backgroundColor: hex2rgba(s.accent, 0.14), borderColor: hex2rgba(s.accent, 0.28) }]}>
                        <Ionicons name={s.icon} size={30} color={s.accent} />
                      </View>
                      <Text style={styles.svcName}>{s.name}</Text>
                      <Text style={styles.svcDesc}>{s.desc}</Text>
                      <View style={[styles.pricePill, { backgroundColor: hex2rgba(s.accent, 0.12), borderColor: hex2rgba(s.accent, 0.28), marginTop: 12 }]}>
                        <Text style={[styles.priceText, { color: s.accent }]}>{s.price}</Text>
                      </View>
                    </LinearGradient>
                  </TouchableOpacity>
                ))}
              </ScrollView>
            )}
          </View>

          {/* ══ MACHINES ════════════════════════════════════════════════════════ */}
          <View style={styles.section}>
            <SectionHead
              eyebrow="OUR EQUIPMENT"
              title="Professional Machines"
              subtitle="State-of-the-art LG commercial washers and dryers"
              accentColor={C.violet}
            />
            
            <FlatList
              ref={machineRef}
              data={MACHINES}
              renderItem={renderMachine}
              keyExtractor={(_, i) => String(i)}
              horizontal
              pagingEnabled
              showsHorizontalScrollIndicator={false}
              snapToInterval={CARD_WIDTH + 16}
              snapToAlignment="start"
              decelerationRate="fast"
              contentContainerStyle={{ paddingHorizontal: 24, gap: 16 }}
              onViewableItemsChanged={onMachineView}
              viewabilityConfig={viewConfig}
              getItemLayout={(_, i) => ({ length: CARD_WIDTH + 16, offset: (CARD_WIDTH + 16) * i, index: i })}
              removeClippedSubviews={true}
              maxToRenderPerBatch={2}
              windowSize={3}
              initialNumToRender={1}
            />
            <View style={{ marginTop: 18 }}>
              <Dots count={MACHINES.length} active={activeMachine} color={C.violet} />
            </View>
          </View>

          {/* ══ FEATURES ════════════════════════════════════════════════════════ */}
          <View style={[styles.section, { paddingTop: 0 }]}>
            <SectionHead eyebrow="WHY US" title="Quality Guarantee" accentColor={C.emerald} />
            <View style={styles.featGrid}>
              {FEATURES.map((f, i) => (
                <View key={i} style={styles.featCard}>
                  <View style={[styles.featIcon, { backgroundColor: hex2rgba(f.color, 0.12), borderColor: hex2rgba(f.color, 0.25) }]}>
                    <Ionicons name={f.icon} size={22} color={f.color} />
                  </View>
                  <Text style={styles.featLabel}>{f.label}</Text>
                  <Text style={styles.featDesc}>{f.desc}</Text>
                </View>
              ))}
            </View>
          </View>

          {/* ══ FEEDBACKS ═══════════════════════════════════════════════════════ */}
          {feedbacks.length > 0 && (
            <View style={styles.section}>
              <SectionHead
                eyebrow="CUSTOMER LOVE"
                title="What They Say"
                subtitle="Real words from real customers"
                accentColor={C.amber}
              />
              <FlatList
                ref={feedRef}
                data={feedbacks}
                renderItem={renderFeedback}
                keyExtractor={i => String(i.id)}
                horizontal
                pagingEnabled
                showsHorizontalScrollIndicator={false}
                snapToInterval={CARD_WIDTH + 16}
                decelerationRate="fast"
                contentContainerStyle={{ paddingHorizontal: 24, gap: 16 }}
                onViewableItemsChanged={onFeedView}
                viewabilityConfig={viewConfig}
                getItemLayout={(_, i) => ({ length: CARD_WIDTH + 16, offset: (CARD_WIDTH + 16) * i, index: i })}
                removeClippedSubviews={true}
                maxToRenderPerBatch={3}
                windowSize={5}
                initialNumToRender={2}
              />
              <View style={{ marginTop: 18 }}>
                <Dots count={feedbacks.length} active={activeFeed} color={C.amber} />
              </View>
            </View>
          )}

          {/* ══ BRANCHES ════════════════════════════════════════════════════════ */}
          {branches.length > 0 && (
            <View style={styles.section}>
              <SectionHead
                eyebrow="FIND US"
                title="Our Branches"
                subtitle="Serving Sibulan · Siaton · Bais City"
                accentColor={C.violet}
              />
              <FlatList
                ref={branchRef}
                data={branches}
                renderItem={renderBranch}
                keyExtractor={i => String(i.id)}
                horizontal
                pagingEnabled
                showsHorizontalScrollIndicator={false}
                snapToInterval={CARD_WIDTH + 16}
                decelerationRate="fast"
                contentContainerStyle={{ paddingHorizontal: 24, gap: 16 }}
                onViewableItemsChanged={onBranchView}
                viewabilityConfig={viewConfig}
                getItemLayout={(_, i) => ({ length: CARD_WIDTH + 16, offset: (CARD_WIDTH + 16) * i, index: i })}
                removeClippedSubviews={true}
                maxToRenderPerBatch={2}
                windowSize={3}
                initialNumToRender={1}
              />
              <View style={{ marginTop: 18 }}>
                <Dots count={branches.length} active={activeBranch} color={C.violet} />
              </View>
            </View>
          )}

          {/* ══ BOTTOM CTA ══════════════════════════════════════════════════════ */}
          <View style={styles.bottomCTA}>
            <LinearGradient colors={['#1a3a6e', '#0c1e4a']} style={styles.bottomCard}>

              {/* Decorative orb */}
              <View style={[styles.orb, { top:-50, right:-50, width:160, height:160, backgroundColor: hex2rgba(C.sky, 0.14) }]} />

              <View style={[styles.ctaIconRing, { borderColor: hex2rgba(C.sky, 0.35) }]}>
                <Ionicons name="water" size={28} color={C.sky} />
              </View>
              <Text style={styles.ctaTitle}>Ready to get started?</Text>
              <Text style={styles.ctaSubtitle}>
                Create an account to book your first laundry service or track your order in real time.
              </Text>

              <TouchableOpacity
                onPress={() => router.push('/(auth)/register')}
                activeOpacity={0.85}
                style={{ width:'100%' }}
              >
                <LinearGradient colors={SKY_GRADIENT} style={styles.ctaPrimary}>
                  <Text style={styles.ctaPrimaryText}>Create Free Account</Text>
                  <Ionicons name="person-add-outline" size={18} color="#fff" />
                </LinearGradient>
              </TouchableOpacity>

              <TouchableOpacity
                onPress={() => router.push('/(auth)/login')}
                style={styles.ctaSecondary}
                activeOpacity={0.75}
              >
                <Text style={styles.ctaSecondaryText}>I already have an account</Text>
              </TouchableOpacity>
            </LinearGradient>
          </View>

        </Animated.View>
      </ScrollView>
    </View>
  );
}

// ─── Styles ───────────────────────────────────────────────────────────────────
const styles = StyleSheet.create({
  root: { flex:1, backgroundColor: C.bg },

  // ── Hero ──
  hero: { paddingTop: Platform.OS === 'ios' ? 60 : StatusBar.currentHeight + 16, paddingBottom: 36 },
  heroInner: { paddingHorizontal: 24, alignItems: 'center' },
  orb: { position:'absolute', width:240, height:240, borderRadius:120 },

  wordmarkRow: { flexDirection:'row', alignItems:'center', gap:14, marginBottom:20, alignSelf:'flex-start' },
  logoBorder: { width:52, height:52, borderRadius:14, overflow:'hidden', borderWidth:2, borderColor:'rgba(255,255,255,0.25)', backgroundColor:'rgba(255,255,255,0.1)' },
  logo: { width:'100%', height:'100%' },
  heroTitle: { fontSize:28, fontWeight:'900', color:C.white, letterSpacing:-0.5 },
  heroClaim: { fontSize:13, color:'rgba(255,255,255,0.72)', fontWeight:'500', marginTop:2 },

  heroDesc: { fontSize:14, color:'rgba(255,255,255,0.7)', lineHeight:22, textAlign:'center', marginBottom:24, paddingHorizontal:8 },

  heroCarousel: { width:'100%', height:210, borderRadius:20, overflow:'hidden', marginBottom:16, backgroundColor:C.surfaceUp },
  heroCarouselImg: { width:'100%', height:'100%', resizeMode:'cover' },
  slideBadge: { position:'absolute', bottom:12, right:12, backgroundColor:'rgba(0,0,0,0.55)', paddingHorizontal:10, paddingVertical:4, borderRadius:20, borderWidth:1, borderColor:'rgba(255,255,255,0.15)' },
  slideBadgeText: { fontSize:11, color:'#fff', fontWeight:'700', letterSpacing:1 },

  heroCTA: { flexDirection:'row', alignItems:'center', justifyContent:'center', gap:10, paddingVertical:16, borderRadius:40 },
  heroCTAText: { fontSize:16, fontWeight:'800', color:'#fff', letterSpacing:0.3 },

  loginLink: { marginTop:16, paddingVertical:6 },
  loginLinkText: { fontSize:13, color:'rgba(255,255,255,0.55)', textAlign:'center' },

  // ── Stats ──
  statsBar: { flexDirection:'row', backgroundColor:C.surface, borderBottomWidth:1, borderTopWidth:1, borderColor:C.border },
  statItem: { flex:1, alignItems:'center', paddingVertical:16 },
  statDivider: { borderRightWidth:1, borderRightColor:C.border },
  statValue: { fontSize:20, fontWeight:'900', color:C.white, letterSpacing:-0.3 },
  statLabel: { fontSize:11, color:C.muted, marginTop:3, fontWeight:'600', letterSpacing:0.5, textTransform:'uppercase' },

  // ── Section ──
  section: { paddingVertical:40 },
  eyebrowPill: { flexDirection:'row', alignItems:'center', gap:6, borderWidth:1, paddingHorizontal:12, paddingVertical:5, borderRadius:30, marginBottom:12 },
  eyebrowDot: { width:6, height:6, borderRadius:3 },
  eyebrow: { fontSize:10, fontWeight:'800', letterSpacing:1.8, textTransform:'uppercase' },
  sectionTitle: { fontSize:26, fontWeight:'900', color:C.white, textAlign:'center', letterSpacing:-0.4, marginBottom:8 },
  sectionSub: { fontSize:14, color:C.text, textAlign:'center', lineHeight:20 },

  // ── Flat card (shared) ──
  flatCard: { width: CARD_WIDTH, borderRadius:24, overflow:'hidden', backgroundColor:C.surface, borderWidth:1, borderColor:C.border },

  // ── Service card ──
  svcGradient: { padding:24, minHeight:240, justifyContent:'space-between' },
  svcBgImage: { position: 'absolute', width: '100%', height: '100%', top: 0, left: 0 },
  svcTopRow: { flexDirection:'row', justifyContent:'space-between', alignItems:'flex-start', marginBottom:20 },
  svcIconBubble: { width:68, height:68, borderRadius:18, justifyContent:'center', alignItems:'center', borderWidth:1.5 },
  svcArrowBtn: { width:36, height:36, borderRadius:12, borderWidth:1.5, justifyContent:'center', alignItems:'center' },
  svcName: { fontSize:22, fontWeight:'900', color:C.white, marginBottom:8, letterSpacing:-0.4 },
  svcDesc: { fontSize:13, color:C.text, lineHeight:20, marginBottom:16, opacity:0.9 },
  svcFooter: { flexDirection:'row', alignItems:'center', flexWrap:'wrap', gap:8 },
  pricePill: { flexDirection:'row', alignItems:'center', gap:6, paddingHorizontal:14, paddingVertical:8, borderRadius:30, borderWidth:1.5 },
  priceText: { fontSize:13, fontWeight:'800', letterSpacing:0.2 },
  timePill: { flexDirection:'row', alignItems:'center', gap:5, backgroundColor:'rgba(255,255,255,0.06)', paddingHorizontal:12, paddingVertical:7, borderRadius:20 },
  timeText: { fontSize:12, color:C.muted, fontWeight:'600' },

  // ── Features ──
  featGrid: { flexDirection:'row', flexWrap:'wrap', gap:12, paddingHorizontal:24 },
  featCard: { width:(SCREEN_WIDTH - 60) / 2, backgroundColor:C.surface, borderRadius:18, padding:16, borderWidth:1, borderColor:C.border },
  featIcon: { width:44, height:44, borderRadius:12, justifyContent:'center', alignItems:'center', borderWidth:1, marginBottom:12 },
  featLabel: { fontSize:13, fontWeight:'800', color:C.white, marginBottom:5 },
  featDesc: { fontSize:11, color:C.muted, lineHeight:16 },

  // ── Feedback ──
  feedCard: { padding:24, minHeight:220 },
  feedQuoteMark: { fontSize:52, lineHeight:44, color:C.sky, opacity:0.25, fontWeight:'300', marginBottom:6, marginTop:10 },
  feedText: { fontSize:14, color:C.text, lineHeight:23, fontStyle:'italic', marginBottom:20 },
  feedAuthorRow: { flexDirection:'row', alignItems:'center', gap:12, borderTopWidth:1, borderTopColor:C.border, paddingTop:16 },
  feedAvatar: { width:44, height:44, borderRadius:22, justifyContent:'center', alignItems:'center' },
  feedAvatarLetter: { fontSize:18, fontWeight:'800', color:'#fff' },
  feedName: { fontSize:14, fontWeight:'800', color:C.white, marginBottom:3 },
  feedBranch: { fontSize:11, color:C.sky, fontWeight:'700', letterSpacing:0.8, textTransform:'uppercase' },

  // ── Branch ──
  branchCard: { minHeight: 260 },
  branchGrad: { padding:24, flex:1 },
  branchIconBubble: { width:60, height:60, borderRadius:16, justifyContent:'center', alignItems:'center', borderWidth:1.5, marginBottom:16 },
  branchName: { fontSize:22, fontWeight:'900', color:C.white, marginBottom:6, letterSpacing:-0.4 },
  branchAddr: { fontSize:13, color:C.text, lineHeight:20, marginBottom:16, opacity:0.85 },
  branchMeta: { flexDirection:'row', flexWrap:'wrap', gap:8 },
  metaPill: { flexDirection:'row', alignItems:'center', gap:6, paddingHorizontal:12, paddingVertical:7, borderRadius:20, borderWidth:1 },
  metaText: { fontSize:12, fontWeight:'700' },
  metaTextMuted: { fontSize:12, color:C.muted, fontWeight:'500' },
  statusDot: { width:7, height:7, borderRadius:4 },

  // ── Machine ──
  machineCard: { minHeight: 320 },
  machineBgImage: { position: 'absolute', width: '100%', height: '100%', top: 0, left: 0 },
  machineGrad: { padding: 24, flex: 1, justifyContent: 'space-between' },
  machineIconBubble: { width: 64, height: 64, borderRadius: 18, justifyContent: 'center', alignItems: 'center', borderWidth: 1.5, marginBottom: 16 },
  machineName: { fontSize: 20, fontWeight: '900', color: C.white, marginBottom: 8, letterSpacing: -0.4 },
  machineDesc: { fontSize: 13, color: C.text, lineHeight: 20, marginBottom: 16, opacity: 0.9 },
  capacityPill: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 12, paddingVertical: 8, borderRadius: 20, borderWidth: 1, alignSelf: 'flex-start', marginBottom: 12 },
  capacityText: { fontSize: 12, fontWeight: '700' },
  machineFeatures: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
  featurePill: { flexDirection: 'row', alignItems: 'center', gap: 4, backgroundColor: 'rgba(255,255,255,0.08)', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 16 },
  featureText: { fontSize: 11, color: C.text, fontWeight: '600' },

  // ── Bottom CTA ──
  bottomCTA: { paddingHorizontal:24, paddingBottom:16 },
  bottomCard: { borderRadius:28, padding:32, alignItems:'center', overflow:'hidden', borderWidth:1, borderColor:'rgba(56,189,248,0.15)' },
  ctaIconRing: { width:64, height:64, borderRadius:32, borderWidth:2, justifyContent:'center', alignItems:'center', marginBottom:20, backgroundColor:'rgba(56,189,248,0.08)' },
  ctaTitle: { fontSize:24, fontWeight:'900', color:C.white, textAlign:'center', marginBottom:10, letterSpacing:-0.3 },
  ctaSubtitle: { fontSize:14, color:'rgba(255,255,255,0.65)', textAlign:'center', lineHeight:22, marginBottom:28 },
  ctaPrimary: { flexDirection:'row', alignItems:'center', justifyContent:'center', gap:10, paddingVertical:16, borderRadius:40, width:'100%' },
  ctaPrimaryText: { fontSize:15, fontWeight:'800', color:'#fff' },
  ctaSecondary: { marginTop:14, paddingVertical:8 },
  ctaSecondaryText: { fontSize:13, color:'rgba(255,255,255,0.45)', textAlign:'center', fontWeight:'600' },
});
