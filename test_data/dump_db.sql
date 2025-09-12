--
-- PostgreSQL database dump
--

-- Dumped from database version 16.6
-- Dumped by pg_dump version 16.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public."user" (id, login, password, email, is_verified, last_active, days_without_break) FROM stdin;
4	funt_14	$2y$13$K8fVvL9c62iY1TmuuLOb5eGaZFOSd6XxPcTRsN.csXnKgy3qunoXu	funt_14@example.com	t	2025-05-11 11:41:55	12
1	admin	$2y$13$nnfpJX0SosRwiKEwXGYRme1BUVoCSjpb9xmvAQoP67bD5mZs/qjzK	admin@example.com	t	2025-05-11 11:48:33	1
2	petra_n91	$2y$13$f4B4sK/b7Ok33pfYjPFU7.LDteD7FNaosifxI8peHcchM5jwMKOYi	petra.novakova91@example.com	t	2025-05-11 17:35:57	1
3	tomik_d87	$2y$13$itgmzxt7dqSQE98MYXybNe63301PwwpytMdrgfR56ulX1Yd2oz1E2	tomas.dvorak87@example.com	f	2025-05-11 17:47:26	2
\.


--
-- Data for Name: bonus; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.bonus (id, owner_id, type, granted_at, is_used) FROM stdin;
1	3	miss_day	2025-05-10 11:50:21	f
2	3	successful_test	2025-05-10 11:50:37	f
3	4	miss_day	2025-05-01 13:43:21	f
4	4	miss_day	2025-04-27 13:43:36	f
5	4	miss_day	2025-04-23 13:44:03	f
\.


--
-- Data for Name: deck; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.deck (id, parent_id, owner_id, name, is_private, about) FROM stdin;
1	\N	4	Slovní druhy v češtině	t	Podstatná jména, slovesa, příslovce... Naučte se, jak je správně rozeznat a používat.
4	\N	2	Zdravá výživa	t	Kolekce zaměřená na zdravou výživu, vyváženou stravu a tipy pro lepší životní styl. Obsahuje recepty a výživové doporučení.
6	\N	3	Zdravotní péče	t	
9	\N	1	Němčina pro turisty	t	
10	\N	2	Fráze pro cestování v angličtině	f	Užitečné fráze a výrazy pro komunikaci v angličtině při cestování po anglicky mluvících zemích.
2	10	2	Fráze pro cestování v angličtině	t	Užitečné fráze a výrazy pro komunikaci v angličtině při cestování po anglicky mluvících zemích.
11	\N	3	Ruský jazyk pro začátečníky	f	
5	11	3	Ruský jazyk pro začátečníky	t	
12	10	4	Fráze pro cestování v angličtině	t	Užitečné fráze a výrazy pro komunikaci v angličtině při cestování po anglicky mluvících zemích.
13	3	1	Základy španělštiny	t	Základní fráze a slovní zásoba pro začátečníky. Pomůže vám začít komunikovat ve španělsky mluvících zemích.
14	\N	2	Základy španělštiny	f	Základní fráze a slovní zásoba pro začátečníky. Pomůže vám začít komunikovat ve španělsky mluvících zemích.
3	14	2	Základy španělštiny	t	Základní fráze a slovní zásoba pro začátečníky. Pomůže vám začít komunikovat ve španělsky mluvících zemích.
\.


--
-- Data for Name: card; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.card (id, deck_id, front_side, back_side, to_learn, last_learned, learn_score, front_image, back_image) FROM stdin;
1	9	Wo ist die nächste U-Bahn-Station?	Kde je nejbližší stanice metra?	\N	\N	\N	\N	\N
2	9	Ich hätte gerne ein Bier, bitte	Dal(a) bych si jedno pivo, prosím	\N	\N	\N	\N	\N
4	9	Können Sie das bitte wiederholen?	Můžete to prosím zopakovat?	\N	\N	\N	\N	\N
5	9	Ich spreche nur ein wenig Deutsch	Mluvím jen trochu německy	\N	\N	\N	\N	\N
6	9	Wo ist die Toilette?	Kde je toaleta?	\N	\N	\N	\N	\N
10	9	Ich bin Tourist	Jsem turista	\N	\N	\N	\N	\N
21	10	Where is the nearest bus stop?	Kde je nejbližší autobusová zastávka?	\N	\N	\N	\N	\N
22	10	How much is a ticket to the city center?	Kolik stojí lístek do centra?	\N	\N	\N	\N	\N
23	10	I have a reservation under the name Novák.	Mám rezervaci na jméno Novák.	\N	\N	\N	\N	\N
24	10	Can you recommend a good local restaurant?	Můžete doporučit nějakou dobrou místní restauraci?	\N	\N	\N	\N	\N
25	10	What time does the train leave?	V kolik hodin odjíždí vlak?	\N	\N	\N	\N	\N
26	10	I would like to check in, please.	Rád(a) bych se přihlásil(a), prosím.	\N	\N	\N	\N	\N
27	10	Is there Wi-Fi here?	Je tady Wi-Fi?	\N	\N	\N	\N	\N
28	10	I don’t speak English very well.	Nemluvím moc dobře anglicky.	\N	\N	\N	\N	\N
29	10	Can I pay by card?	Můžu platit kartou?	\N	\N	\N	\N	\N
30	10	Help! I need assistance.	Pomoc! Potřebuji pomoc.	\N	\N	\N	\N	\N
63	11	Как дела?	Jak se máš? (Kak děla?)	\N	\N	\N	\N	\N
44	4	Losos	Zdroj omega-3 mastných kyselin, prospěšný pro srdce	2025-05-11 10:08:14	2025-05-11 09:08:14	2	\N	812KtHd0UIL._AC_UL800_QL65_.jpg
41	4	Cizrnový hummus	Zdravý dip z cizrny, tahini a citronu. Skvělý zdroj bílkovin	2025-05-11 10:08:01	2025-05-11 09:08:01	2	\N	wsi-imageoptim-hummus-horiz-a-1200-1.jpg
70	11	Привет	Ahoj (Privet)	\N	\N	\N	\N	\N
13	2	I have a reservation under the name Novák.	Mám rezervaci na jméno Novák.	2025-05-12 09:08:49	2025-05-11 09:08:49	3	\N	\N
45	4	Quinoa	Bezlepková obilovina s vysokým obsahem bílkovin a vlákniny	\N	\N	\N	\N	quinoa.jpg
37	3	¿Cuánto cuesta?	Kolik to stojí?	2025-05-11 09:59:39	2025-05-11 08:59:39	2	\N	\N
34	3	Me llamo Ana	Jmenuji se Ana	2025-05-12 09:05:36	2025-05-11 09:05:36	3	\N	\N
31	3	Hola	Ahoj / Dobrý den	2025-05-12 09:05:41	2025-05-11 09:05:41	3	\N	\N
39	3	Lo siento	Promiň / Je mi líto	2025-05-11 10:05:49	2025-05-11 09:05:49	2	\N	\N
38	3	No entiendo	Nerozumím	2025-05-11 10:05:55	2025-05-11 09:05:55	2	\N	\N
35	3	Por favor	Prosím	2025-05-12 09:06:01	2025-05-11 09:06:01	3	\N	\N
36	3	Gracias	Děkuji	2025-05-12 09:06:05	2025-05-11 09:06:05	3	\N	\N
32	3	¿Cómo estás?	Jak se máš?	2025-05-12 09:06:10	2025-05-11 09:06:10	3	\N	\N
62	11	Здравствуйте	Dobrý den (Zdravstvujtye)	\N	\N	\N	\N	\N
12	2	How much is a ticket to the city center?	Kolik stojí lístek do centra?	2025-05-12 09:08:54	2025-05-11 09:08:54	3	\N	\N
17	2	Is there Wi-Fi here?	Je tady Wi-Fi?	2025-05-12 09:08:59	2025-05-11 09:08:59	3	\N	\N
18	2	I don’t speak English very well.	Nemluvím moc dobře anglicky.	2025-05-12 09:09:04	2025-05-11 09:09:04	3	\N	\N
19	2	Can I pay by card?	Můžu platit kartou?	2025-05-12 09:09:09	2025-05-11 09:09:09	3	\N	\N
20	2	Help! I need assistance.	Pomoc! Potřebuji pomoc.	2025-05-12 09:09:15	2025-05-11 09:09:15	3	\N	\N
14	2	Can you recommend a good local restaurant?	Můžete doporučit nějakou dobrou místní restauraci?	2025-05-12 09:09:19	2025-05-11 09:09:19	3	\N	\N
15	2	What time does the train leave?	V kolik hodin odjíždí vlak?	2025-05-12 09:09:24	2025-05-11 09:09:24	3	\N	\N
16	2	I would like to check in, please.	Rád(a) bych se přihlásil(a), prosím.	2025-05-12 09:09:30	2025-05-11 09:09:30	3	\N	\N
33	3	Bien, gracias. ¿Y tú?	Dobře, děkuji. A ty?	2025-05-11 09:44:28	2025-05-11 09:06:17	2	\N	\N
11	2	Where is the nearest bus stop?	Kde je nejbližší autobusová zastávka?	2025-05-11 09:12:15	2025-05-11 09:09:35	2	\N	\N
8	9	Ich habe eine Reservierung	Mám rezervaci	2025-05-11 12:48:27	2025-05-11 11:48:27	2	\N	\N
9	9	Hilfe!	Pomoc!	2025-05-11 12:48:29	2025-05-11 11:48:29	2	\N	\N
7	9	Gibt es hier ein gutes Restaurant?	Je tady nějaká dobrá restaurace?	2025-05-11 12:48:31	2025-05-11 11:48:31	2	\N	\N
3	9	Wie viel kostet das?	Kolik to stojí?	2025-05-11 12:48:33	2025-05-11 11:48:33	2	\N	\N
42	4	Chia semínka	Malá semínka bohatá na vlákninu, bílkoviny a omega-3	2025-05-12 17:35:08	2025-05-11 17:35:08	3	\N	6616476621.jpg
40	4	Avokádo	Bohaté na zdravé tuky a vitamíny. Skvělé do salátů nebo toastu	2025-05-12 17:35:29	2025-05-11 17:35:29	3	\N	12273-m.jpg
43	4	Špenát	Listová zelenina plná železa, vápníku a vitamínů	2025-05-12 17:35:57	2025-05-11 17:35:57	3	\N	b60b51f5.jpg
67	11	Я не понимаю	Nerozumím (Ja ně panimáju)	\N	\N	\N	\N	\N
66	11	Извините	Promiňte (Izvinítje)	\N	\N	\N	\N	\N
65	11	Пожалуйста	Prosím / Není zač (Pážalujsta)	\N	\N	\N	\N	\N
69	11	До свидания	Na shledanou (Da svidánija)	\N	\N	\N	\N	\N
64	11	Спасибо	Děkuji (Spasíba)	\N	\N	\N	\N	\N
68	11	Сколько это стоит?	Kolik to stojí? (Skólka eta stóit?)	\N	\N	\N	\N	\N
49	5	Спасибо	Děkuji (Spasíba)	2025-05-11 10:41:42	2025-05-10 09:47:09	2	\N	\N
60	5	Я немного говорю по-русски	Mluvím trochu rusky (Ja němnóžka gavarjú pa-rússki)	2025-05-11 10:41:42	2025-05-11 09:47:59	2	\N	\N
59	5	Я из Тулы	Jsem z Tuly (Ja iz Tuly)	2025-05-11 10:41:42	2025-05-10 09:46:59	2	\N	\N
78	6	Sanitka	Vozidlo pro převoz pacientů k ošetření nebo do nemocnice	\N	\N	\N	Sanitka_Melnik.jpg	\N
79	6	Pacient	Osoba, která vyhledá lékařskou pomoc	\N	\N	\N	\N	\N
80	6	Lékař	Zdravotnický pracovník, který diagnostikuje a léčí nemoci	\N	\N	\N	\N	\N
81	6	Lékárna	Místo, kde si můžete vyzvednout léky na předpis i bez něj	\N	\N	\N	\N	\N
82	6	Injekce	Způsob podání léku jehlou přímo do těla	\N	\N	\N	7145978818.jpg	\N
83	6	Stetoskop	Nástroj používaný k poslechu srdce a plic	\N	\N	\N	6277116189.jpg	\N
84	6	Obvaz	Materiál pro zakrytí a ochranu ran	\N	\N	\N	6197326318.jpg	\N
85	6	Teploměr	Přístroj na měření tělesné teploty	\N	\N	\N	023ab88ff040be5d0bc47c766f39e24b.jpeg	\N
115	14	¿Cuánto cuesta?	Kolik to stojí?	\N	\N	\N	\N	\N
116	14	Me llamo Ana	Jmenuji se Ana	\N	\N	\N	\N	\N
117	14	Hola	Ahoj / Dobrý den	\N	\N	\N	\N	\N
118	14	Lo siento	Promiň / Je mi líto	\N	\N	\N	\N	\N
119	14	No entiendo	Nerozumím	\N	\N	\N	\N	\N
120	14	Por favor	Prosím	\N	\N	\N	\N	\N
121	14	Gracias	Děkuji	\N	\N	\N	\N	\N
122	14	¿Cómo estás?	Jak se máš?	\N	\N	\N	\N	\N
123	14	Bien, gracias. ¿Y tú?	Dobře, děkuji. A ty?	\N	\N	\N	\N	\N
97	12	How much is a ticket to the city center?	Kolik stojí lístek do centra?	2025-05-12 11:33:31	2025-05-11 11:33:31	3	\N	\N
104	12	Can I pay by card?	Můžu platit kartou?	2025-05-12 11:33:33	2025-05-11 11:33:33	3	\N	\N
96	12	Where is the nearest bus stop?	Kde je nejbližší autobusová zastávka?	2025-05-12 11:33:36	2025-05-11 11:33:36	3	\N	\N
100	12	What time does the train leave?	V kolik hodin odjíždí vlak?	2025-05-12 11:33:38	2025-05-11 11:33:38	3	\N	\N
103	12	I don’t speak English very well.	Nemluvím moc dobře anglicky.	2025-05-12 11:33:40	2025-05-11 11:33:40	3	\N	\N
102	12	Is there Wi-Fi here?	Je tady Wi-Fi?	2025-05-12 11:33:43	2025-05-11 11:33:43	3	\N	\N
99	12	Can you recommend a good local restaurant?	Můžete doporučit nějakou dobrou místní restauraci?	2025-05-12 11:33:45	2025-05-11 11:33:45	3	\N	\N
105	12	Help! I need assistance.	Pomoc! Potřebuji pomoc.	2025-05-12 11:33:48	2025-05-11 11:33:48	3	\N	\N
101	12	I would like to check in, please.	Rád(a) bych se přihlásil(a), prosím.	2025-05-12 11:33:50	2025-05-11 11:33:50	3	\N	\N
98	12	I have a reservation under the name Novák.	Mám rezervaci na jméno Novák.	2025-05-12 11:33:53	2025-05-11 11:33:53	3	\N	\N
114	13	Lo siento	Promiň / Je mi líto	2025-05-11 11:47:50	2025-05-11 11:46:50	1	\N	\N
112	13	¿Cuánto cuesta?	Kolik to stojí?	2025-05-11 11:47:53	2025-05-11 11:46:53	1	\N	\N
107	13	¿Cómo estás?	Jak se máš?	2025-05-11 11:47:55	2025-05-11 11:46:55	1	\N	\N
108	13	Bien, gracias. ¿Y tú?	Dobře, děkuji. A ty?	2025-05-11 11:47:57	2025-05-11 11:46:57	1	\N	\N
110	13	Por favor	Prosím	2025-05-11 11:48:00	2025-05-11 11:47:00	1	\N	\N
109	13	Me llamo Ana	Jmenuji se Ana	2025-05-11 11:48:02	2025-05-11 11:47:02	1	\N	\N
106	13	Hola	Ahoj / Dobrý den	2025-05-11 11:48:05	2025-05-11 11:47:05	1	\N	\N
111	13	Gracias	Děkuji	2025-05-11 11:48:07	2025-05-11 11:47:07	1	\N	\N
113	13	No entiendo	Nerozumím	2025-05-11 11:48:10	2025-05-11 11:47:10	1	\N	\N
73	11	Откуда вы?	Odkud jste? (Atkúda vy?)	\N	\N	\N	\N	\N
94	1	Částice (partikule)	Vyjadřuje různé postoje mluvčího, zesiluje význam. Např.: ať, nechť, právě, jen	2025-05-11 11:42:53	2025-05-11 11:41:53	1	\N	\N
54	5	До свидания	Na shledanou (Da svidánija)	2025-05-12 09:47:43	2025-05-10 09:47:43	3	\N	\N
91	1	Příslovce (adverbium)	Vyjadřuje okolnosti děje: místo, čas, způsob. Např.: dnes, rychle, doma	2025-05-11 11:42:55	2025-05-11 11:41:55	1	\N	\N
95	1	Citoslovce (interjekce)	Vyjadřuje city, zvuky, výzvy. Např.: ach, bác, hej, fuj	\N	\N	\N	\N	\N
53	5	Сколько это стоит?	Kolik to stojí? (Skólka eta stóit?)	2025-05-12 09:47:38	2025-05-10 09:47:38	3	\N	\N
74	11	Я из Тулы	Jsem z Tuly (Ja iz Tuly)	\N	\N	\N	\N	\N
71	11	Да / Нет	Ano / Ne (Da / Nyet)	\N	\N	\N	\N	\N
76	11	Можно счёт, пожалуйста?	Můžu prosit účet? (Mózhna ščót, pažálujsta?)	\N	\N	\N	\N	\N
72	11	Как вас зовут?	Jak se jmenujete? (Kak vas zavút?)	\N	\N	\N	\N	\N
77	11	Меня зовут Саша	Jmenuji se Saša (Menja zavut Saša)	\N	\N	\N	\N	\N
75	11	Я немного говорю по-русски	Mluvím trochu rusky (Ja němnóžka gavarjú pa-rússki)	\N	\N	\N	\N	\N
46	5	Здравствуйте	Dobrý den (Zdravstvujtye)	2025-05-12 09:47:28	2025-05-10 09:47:28	3	\N	\N
88	1	Zájmeno (pronominum)	Zastupuje nebo ukazuje na podstatné jméno. Např.: já, on, můj, který	\N	\N	\N	\N	\N
55	5	Привет	Ahoj (Privet)	2025-05-12 09:46:50	2025-05-10 09:46:50	3	\N	\N
47	5	Как дела?	Jak se máš? (Kak děla?)	2025-05-12 09:47:14	2025-05-10 09:47:14	3	\N	\N
89	1	Číslovka (numerale)	Vyjadřuje počet, pořadí, množství. Např.: jedna, tři, první, mnoho	\N	\N	\N	\N	\N
92	1	Předložka (prepozice)	Vyjadřuje vztahy mezi slovy ve větě. Např.: v, na, s, bez	2025-05-11 11:42:51	2025-05-11 11:41:51	1	\N	\N
51	5	Извините	Promiňte (Izvinítje)	2025-05-12 09:47:48	2025-05-10 09:47:48	3	\N	\N
52	5	Я не понимаю	Nerozumím (Ja ně panimáju)	2025-05-12 09:47:18	2025-05-10 09:47:04	3	\N	\N
58	5	Откуда вы?	Odkud jste? (Atkúda vy?)	2025-05-12 09:46:45	2025-05-10 09:46:45	3	\N	\N
61	5	Можно счёт, пожалуйста?	Můžu prosit účet? (Mózhna ščót, pažálujsta?)	2025-05-11 10:41:42	2025-05-10 09:47:53	2	\N	\N
86	1	Podstatné jméno (substantivum)	Označuje osoby, zvířata, věci, vlastnosti, děje. Např.: stůl, dívka, pes, láska	2025-05-11 11:42:49	2025-05-11 11:41:49	1	\N	\N
90	1	Sloveso (verbum)	Označuje děj, stav, činnost. Např.: jít, spát, být, zpívat	\N	\N	\N	\N	\N
50	5	Пожалуйста	Prosím / Není zač (Pážalujsta)	2025-05-11 10:41:42	2025-05-10 09:47:04	2	\N	\N
48	5	Меня зовут Саша	Jmenuji se Saša (Menja zavut Saša)	2025-05-12 09:47:33	2025-05-10 09:47:33	3	\N	\N
56	5	Да / Нет	Ano / Ne (Da / Nyet)	2025-05-12 09:46:55	2025-05-10 09:46:55	3	\N	\N
57	5	Как вас зовут?	Jak se jmenujete? (Kak vas zavút?)	2025-05-11 17:47:26	2025-05-10 09:47:24	2	\N	\N
93	1	Spojka (konjunkce)	Spojuje věty nebo větné členy. Např.: a, ale, protože, nebo	\N	\N	\N	\N	\N
87	1	Přídavné jméno (adjektivum)	Vyjadřuje vlastnosti podstatných jmen. Např.: červený, malý, unavený	\N	\N	\N	\N	\N
\.


--
-- Data for Name: category; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.category (id, name) FROM stdin;
1	Angličtina
2	Němčina
3	Španělština
4	Čeština
5	Ruština
6	Historie
7	Hudba
8	Medicína
9	Právo
10	Matematika
11	Literatura
12	Chemie
13	Kulinářství
14	Technologie
\.


--
-- Data for Name: deck_category; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.deck_category (deck_id, category_id) FROM stdin;
1	4
2	1
3	3
4	13
5	5
6	8
9	2
10	1
11	5
12	1
13	3
14	3
\.


--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.doctrine_migration_versions (version, executed_at, execution_time) FROM stdin;
DoctrineMigrations\\Version20250510143444	2025-05-10 14:36:08	433
\.


--
-- Data for Name: goal; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.goal (id, owner_id, start_date, end_date, target_cards, achieved_cards, target_tests, achieved_tests, completed, bonus_granted, is_current) FROM stdin;
2	2	2025-05-01 11:16:43	2025-05-08 11:16:52	15	10	\N	\N	f	f	f
3	3	2025-05-03 11:48:26	2025-05-10 11:48:47	16	16	1	1	t	t	f
4	4	2025-05-12 11:42:13	2025-05-19 11:42:13	25	0	\N	\N	f	f	t
1	2	2025-05-11 09:16:17	2025-05-18 09:16:17	20	25	10	3	f	f	t
\.


--
-- Data for Name: messenger_messages; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.messenger_messages (id, body, headers, queue_name, created_at, available_at, delivered_at) FROM stdin;
\.


--
-- Data for Name: notification; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.notification (id, person_to_notificate_id, message, is_read, created_at) FROM stdin;
1	2	Cíl nebyl dosažen. Termín splnění byl do 08.05.2025.	t	2025-05-08 11:18:32
2	4	Učíte se nepřetržitě už 5 dny. Za to získáváte bonus – den pauzy v učení.	t	2025-04-23 13:44:59
3	4	Učíte se nepřetržitě už 10 dny. Za to získáváte bonus – den pauzy v učení.	t	2025-04-27 13:45:19
4	4	Učíte se nepřetržitě už 15 dny. Za to získáváte bonus – den pauzy v učení.	t	2025-05-01 13:45:35
\.


--
-- Data for Name: review; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.review (id, deck_id, reviewed_by_id, rate, description) FROM stdin;
1	12	4	5	Tato kolekce mi opravdu pomohla při cestě do Anglie.
2	10	4	5	Tato kolekce mi opravdu pomohla při cestě do Anglie.
3	13	1	3	Užitečné pro začátek, ale mohlo by být rozšířeno\n
4	3	1	3	Užitečné pro začátek, ale mohlo by být rozšířeno\n
5	14	1	3	Užitečné pro začátek, ale mohlo by být rozšířeno\n
\.


--
-- Data for Name: test; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.test (id, deck_id, started_at, number_of_questions, qurrent_question, finished_at, types_of_questions) FROM stdin;
7	5	2025-05-11 17:45:10	5	5	2025-05-11 17:47:26	[1,2]
1	2	2025-05-11 09:10:36	5	5	2025-05-11 09:12:15	[1,2,3]
2	2	2025-05-11 09:12:45	5	5	2025-05-11 09:13:59	[1]
3	3	2025-05-11 09:40:01	5	5	2025-05-11 09:44:28	[1]
6	5	2025-05-10 10:43:04	5	5	2025-05-10 11:31:46	[1]
\.


--
-- Data for Name: test_result; Type: TABLE DATA; Schema: public; Owner: my_user
--

COPY public.test_result (id, test_id, card_id, user_answer, correct_answer, question_type, question_number, is_correct) FROM stdin;
34	7	57	[false]	[false]	2	3	t
35	7	57	[null]	["Jak se jmenujete?\\n(Kak vas zav\\u00fat?)"]	1	4	f
31	7	55	[false]	[false]	2	0	t
1	1	17	["Je tady Wi-Fi?"]	["Je tady Wi-Fi?"]	3	0	t
2	1	13	["M\\u00e1m rezervaci na jm\\u00e9no Nov\\u00e1k."]	["M\\u00e1m rezervaci na jm\\u00e9no Nov\\u00e1k."]	1	1	t
3	1	13	[false]	[false]	2	2	t
4	1	14	["M\\u016f\\u017eete doporu\\u010dit n\\u011bjakou dobrou m\\u00edstn\\u00ed restauraci?"]	["M\\u016f\\u017eete doporu\\u010dit n\\u011bjakou dobrou m\\u00edstn\\u00ed restauraci?"]	1	3	t
5	1	11	["Kde je autobusova zastavka?"]	["Kde je nejbli\\u017e\\u0161\\u00ed autobusov\\u00e1 zast\\u00e1vka?"]	3	4	f
32	7	53	[true]	[true]	2	1	t
6	2	16	["R\\u00e1d(a) bych se p\\u0159ihl\\u00e1sil(a), pros\\u00edm."]	["R\\u00e1d(a) bych se p\\u0159ihl\\u00e1sil(a), pros\\u00edm."]	1	0	t
7	2	11	["Kde je nejbli\\u017e\\u0161\\u00ed autobusov\\u00e1 zast\\u00e1vka?"]	["Kde je nejbli\\u017e\\u0161\\u00ed autobusov\\u00e1 zast\\u00e1vka?"]	1	1	t
8	2	12	["Kolik stoj\\u00ed l\\u00edstek do centra?"]	["Kolik stoj\\u00ed l\\u00edstek do centra?"]	1	2	t
9	2	13	["M\\u00e1m rezervaci na jm\\u00e9no Nov\\u00e1k."]	["M\\u00e1m rezervaci na jm\\u00e9no Nov\\u00e1k."]	1	3	t
10	2	14	["M\\u016f\\u017eete doporu\\u010dit n\\u011bjakou dobrou m\\u00edstn\\u00ed restauraci?"]	["M\\u016f\\u017eete doporu\\u010dit n\\u011bjakou dobrou m\\u00edstn\\u00ed restauraci?"]	1	4	t
33	7	61	["M\\u016f\\u017eu prosit \\u00fa\\u010det? (M\\u00f3zhna \\u0161\\u010d\\u00f3t, pa\\u017e\\u00e1lujsta?)"]	["M\\u016f\\u017eu prosit \\u00fa\\u010det? (M\\u00f3zhna \\u0161\\u010d\\u00f3t, pa\\u017e\\u00e1lujsta?)"]	1	2	t
11	3	37	["Kolik to stoj\\u00ed?"]	["Kolik to stoj\\u00ed?"]	1	0	t
12	3	35	["Pros\\u00edm"]	["Pros\\u00edm"]	1	1	t
13	3	31	["Ahoj \\/ Dobr\\u00fd den"]	["Ahoj \\/ Dobr\\u00fd den"]	1	2	t
14	3	38	["Nerozum\\u00edm"]	["Nerozum\\u00edm"]	1	3	t
15	3	33	["D\\u011bkuji"]	["Dob\\u0159e, d\\u011bkuji. A ty?"]	1	4	f
26	6	46	["Dobr\\u00fd den\\n(Zdravstvujtye)"]	["Dobr\\u00fd den\\n(Zdravstvujtye)"]	1	0	t
27	6	49	["D\\u011bkuji\\n(Spas\\u00edba)"]	["D\\u011bkuji\\n(Spas\\u00edba)"]	1	1	t
28	6	60	["Mluv\\u00edm trochu rusky\\n(Ja n\\u011bmn\\u00f3\\u017eka gavarj\\u00fa pa-r\\u00fasski)"]	["Mluv\\u00edm trochu rusky\\n(Ja n\\u011bmn\\u00f3\\u017eka gavarj\\u00fa pa-r\\u00fasski)"]	1	2	t
29	6	47	["Jak se m\\u00e1\\u0161?\\n(Kak d\\u011bla?)"]	["Jak se m\\u00e1\\u0161?\\n(Kak d\\u011bla?)"]	1	3	t
30	6	51	["Promi\\u0148te\\n(Izvin\\u00edtje)"]	["Promi\\u0148te\\n(Izvin\\u00edtje)"]	1	4	t
\.


--
-- Name: bonus_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.bonus_id_seq', 5, true);


--
-- Name: card_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.card_id_seq', 123, true);


--
-- Name: category_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.category_id_seq', 14, true);


--
-- Name: deck_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.deck_id_seq', 14, true);


--
-- Name: goal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.goal_id_seq', 5, true);


--
-- Name: messenger_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.messenger_messages_id_seq', 4, true);


--
-- Name: notification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.notification_id_seq', 4, true);


--
-- Name: review_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.review_id_seq', 5, true);


--
-- Name: test_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.test_id_seq', 7, true);


--
-- Name: test_result_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.test_result_id_seq', 35, true);


--
-- Name: user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: my_user
--

SELECT pg_catalog.setval('public.user_id_seq', 4, true);


--
-- PostgreSQL database dump complete
--

