--
-- PostgreSQL database dump
--

\restrict 7YzTrXEPHHuKp3UO25GAsIeJY0Po7rg2lnsvFOYtBuqobxA8mxDdCr9t8gpppQ3

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY public.reservations DROP CONSTRAINT IF EXISTS reservations_user_id_foreign;
ALTER TABLE IF EXISTS ONLY public.reservations DROP CONSTRAINT IF EXISTS reservations_computer_id_foreign;
ALTER TABLE IF EXISTS ONLY public.reservations DROP CONSTRAINT IF EXISTS reservations_approved_by_foreign;
ALTER TABLE IF EXISTS ONLY public.attendance_sessions DROP CONSTRAINT IF EXISTS attendance_sessions_user_id_foreign;
ALTER TABLE IF EXISTS ONLY public.attendance_sessions DROP CONSTRAINT IF EXISTS attendance_sessions_reservation_id_foreign;
ALTER TABLE IF EXISTS ONLY public.attendance_sessions DROP CONSTRAINT IF EXISTS attendance_sessions_computer_id_foreign;
ALTER TABLE IF EXISTS ONLY public.access_codes DROP CONSTRAINT IF EXISTS access_codes_reservation_id_foreign;
DROP INDEX IF EXISTS public.sessions_user_id_index;
DROP INDEX IF EXISTS public.sessions_last_activity_index;
DROP INDEX IF EXISTS public.jobs_queue_index;
DROP INDEX IF EXISTS public.failed_jobs_connection_queue_failed_at_index;
DROP INDEX IF EXISTS public.cache_locks_expiration_index;
DROP INDEX IF EXISTS public.cache_expiration_index;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_pkey;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_nim_unique;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_email_unique;
ALTER TABLE IF EXISTS ONLY public.sessions DROP CONSTRAINT IF EXISTS sessions_pkey;
ALTER TABLE IF EXISTS ONLY public.reservations DROP CONSTRAINT IF EXISTS reservations_pkey;
ALTER TABLE IF EXISTS ONLY public.password_reset_tokens DROP CONSTRAINT IF EXISTS password_reset_tokens_pkey;
ALTER TABLE IF EXISTS ONLY public.migrations DROP CONSTRAINT IF EXISTS migrations_pkey;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS jobs_pkey;
ALTER TABLE IF EXISTS ONLY public.job_batches DROP CONSTRAINT IF EXISTS job_batches_pkey;
ALTER TABLE IF EXISTS ONLY public.failed_jobs DROP CONSTRAINT IF EXISTS failed_jobs_uuid_unique;
ALTER TABLE IF EXISTS ONLY public.failed_jobs DROP CONSTRAINT IF EXISTS failed_jobs_pkey;
ALTER TABLE IF EXISTS ONLY public.computers DROP CONSTRAINT IF EXISTS computers_pkey;
ALTER TABLE IF EXISTS ONLY public.computers DROP CONSTRAINT IF EXISTS computers_computer_code_unique;
ALTER TABLE IF EXISTS ONLY public.cache DROP CONSTRAINT IF EXISTS cache_pkey;
ALTER TABLE IF EXISTS ONLY public.cache_locks DROP CONSTRAINT IF EXISTS cache_locks_pkey;
ALTER TABLE IF EXISTS ONLY public.attendance_sessions DROP CONSTRAINT IF EXISTS attendance_sessions_pkey;
ALTER TABLE IF EXISTS ONLY public.access_codes DROP CONSTRAINT IF EXISTS access_codes_pkey;
ALTER TABLE IF EXISTS ONLY public.access_codes DROP CONSTRAINT IF EXISTS access_codes_code_unique;
ALTER TABLE IF EXISTS public.users ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.reservations ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.migrations ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.jobs ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.failed_jobs ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.computers ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.attendance_sessions ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.access_codes ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE IF EXISTS public.users_id_seq;
DROP TABLE IF EXISTS public.users;
DROP TABLE IF EXISTS public.sessions;
DROP SEQUENCE IF EXISTS public.reservations_id_seq;
DROP TABLE IF EXISTS public.reservations;
DROP TABLE IF EXISTS public.password_reset_tokens;
DROP SEQUENCE IF EXISTS public.migrations_id_seq;
DROP TABLE IF EXISTS public.migrations;
DROP SEQUENCE IF EXISTS public.jobs_id_seq;
DROP TABLE IF EXISTS public.jobs;
DROP TABLE IF EXISTS public.job_batches;
DROP SEQUENCE IF EXISTS public.failed_jobs_id_seq;
DROP TABLE IF EXISTS public.failed_jobs;
DROP SEQUENCE IF EXISTS public.computers_id_seq;
DROP TABLE IF EXISTS public.computers;
DROP TABLE IF EXISTS public.cache_locks;
DROP TABLE IF EXISTS public.cache;
DROP SEQUENCE IF EXISTS public.attendance_sessions_id_seq;
DROP TABLE IF EXISTS public.attendance_sessions;
DROP SEQUENCE IF EXISTS public.access_codes_id_seq;
DROP TABLE IF EXISTS public.access_codes;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: access_codes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.access_codes (
    id bigint NOT NULL,
    reservation_id bigint NOT NULL,
    code character varying(255) NOT NULL,
    code_hash character varying(255) NOT NULL,
    expires_at timestamp(0) without time zone NOT NULL,
    used_at timestamp(0) without time zone,
    revoked_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.access_codes OWNER TO postgres;

--
-- Name: access_codes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.access_codes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.access_codes_id_seq OWNER TO postgres;

--
-- Name: access_codes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.access_codes_id_seq OWNED BY public.access_codes.id;


--
-- Name: attendance_sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attendance_sessions (
    id bigint NOT NULL,
    reservation_id bigint NOT NULL,
    user_id bigint NOT NULL,
    computer_id bigint NOT NULL,
    check_in_at timestamp(0) without time zone NOT NULL,
    scheduled_end_at timestamp(0) without time zone NOT NULL,
    actual_end_at timestamp(0) without time zone,
    status character varying(255) DEFAULT 'ACTIVE'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.attendance_sessions OWNER TO postgres;

--
-- Name: attendance_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.attendance_sessions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.attendance_sessions_id_seq OWNER TO postgres;

--
-- Name: attendance_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.attendance_sessions_id_seq OWNED BY public.attendance_sessions.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: computers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.computers (
    id bigint NOT NULL,
    computer_code character varying(255) NOT NULL,
    computer_name character varying(255) NOT NULL,
    specs character varying(255),
    location character varying(255) DEFAULT 'Lab Data Sains - Gedung Terpadu Lt. 2'::character varying NOT NULL,
    status character varying(255) DEFAULT 'ONLINE_LOCKED'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    maintenance_started_at timestamp(0) without time zone,
    maintenance_until timestamp(0) without time zone,
    maintenance_reason character varying(255)
);


ALTER TABLE public.computers OWNER TO postgres;

--
-- Name: computers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.computers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.computers_id_seq OWNER TO postgres;

--
-- Name: computers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.computers_id_seq OWNED BY public.computers.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: reservations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.reservations (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    computer_id bigint NOT NULL,
    reservation_date date NOT NULL,
    start_time time(0) without time zone NOT NULL,
    end_time time(0) without time zone NOT NULL,
    start_at timestamp(0) without time zone NOT NULL,
    end_at timestamp(0) without time zone NOT NULL,
    purpose character varying(255) NOT NULL,
    notes text,
    status character varying(255) DEFAULT 'PENDING_APPROVAL'::character varying NOT NULL,
    rejection_reason text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    approved_by bigint,
    approved_at timestamp(0) without time zone
);


ALTER TABLE public.reservations OWNER TO postgres;

--
-- Name: reservations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.reservations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.reservations_id_seq OWNER TO postgres;

--
-- Name: reservations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.reservations_id_seq OWNED BY public.reservations.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    nim character varying(255) NOT NULL,
    jurusan character varying(255) DEFAULT 'Teknik Informatika'::character varying NOT NULL,
    email character varying(255),
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    role character varying(255) DEFAULT 'mahasiswa'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_unlimited_session boolean DEFAULT false NOT NULL
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: access_codes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.access_codes ALTER COLUMN id SET DEFAULT nextval('public.access_codes_id_seq'::regclass);


--
-- Name: attendance_sessions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attendance_sessions ALTER COLUMN id SET DEFAULT nextval('public.attendance_sessions_id_seq'::regclass);


--
-- Name: computers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.computers ALTER COLUMN id SET DEFAULT nextval('public.computers_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: reservations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reservations ALTER COLUMN id SET DEFAULT nextval('public.reservations_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: access_codes; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.access_codes VALUES (1, 1, 'LD-7ZOP-8MPT', 'a19ec5e6082fe1b2c3a1c9fd5e5960b4370199a36f48302ea7f989b563e17b5d', '2026-11-03 11:00:00', '2026-11-01 22:12:28', NULL, '2026-11-01 15:09:46', '2026-11-01 15:12:28');
INSERT INTO public.access_codes VALUES (2, 2, 'LD-Z6G4-JGVR', '6ba925c5614dea5e21451bcba2be98ad7a3952749c6a296cc0b39609e63ef227', '2026-11-02 11:00:00', '2026-11-01 22:35:33', NULL, '2026-11-01 15:33:51', '2026-11-01 15:35:33');
INSERT INTO public.access_codes VALUES (3, 3, 'LD-Q3HA-PIWW', '5ab66c2e590c221aa0dbf907ea57c8485db1e080eb5a84f5affe4f94001bb840', '2026-11-02 11:00:00', NULL, NULL, '2026-11-01 15:54:38', '2026-11-01 15:54:38');
INSERT INTO public.access_codes VALUES (4, 4, 'GC83-H4NQ-SQ2B-WDSP', 'd6a8ae80d43c93861a43bfe112874a4b1a7c78914e061dc7c3c6ff30c29ffbc6', '2026-11-02 16:00:00', NULL, NULL, '2026-11-02 11:23:02', '2026-11-02 11:23:02');
INSERT INTO public.access_codes VALUES (5, 5, 'NGTW-9UFE-FE76-AGSB', '251f830ccb77cc096b4b0a6210ea9e504ecc92d8e25b8c89823933fdf6bca547', '2026-11-10 11:00:00', NULL, NULL, '2026-11-02 20:58:28', '2026-11-02 20:58:28');
INSERT INTO public.access_codes VALUES (6, 6, 'BGNA-XK9S-TVEA-TMFP', '2b36d755fdd3e7b00ae9e79be46266e37ebf164e6216b0cc093e6877f9433e69', '2026-11-26 11:00:00', NULL, NULL, '2026-11-02 21:30:09', '2026-11-02 21:30:09');


--
-- Data for Name: attendance_sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.attendance_sessions VALUES (1, 1, 1, 1, '2026-11-01 22:12:19', '2026-11-03 11:00:00', '2026-11-01 22:12:28', 'COMPLETED', '2026-11-01 15:12:19', '2026-11-01 15:12:28');
INSERT INTO public.attendance_sessions VALUES (2, 2, 1, 1, '2026-11-01 22:34:04', '2026-11-02 11:00:00', '2026-11-01 22:35:33', 'COMPLETED', '2026-11-01 15:34:04', '2026-11-01 15:35:33');


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: computers; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.computers VALUES (4, 'PC-04', 'Workstation Modeling & Stats 04', 'Intel Core i5-13500 • 16GB RAM • RTX 3050 8GB • NVMe 512GB', 'Lab Data Sains - Meja 4', 'ONLINE_LOCKED', true, '2026-10-31 12:48:08', '2026-10-31 12:48:08', NULL, NULL, NULL);
INSERT INTO public.computers VALUES (2, 'PC-02', 'Workstation Data Analytics 02', 'Intel Core i7-13700 • 32GB RAM • RTX 3060 12GB • NVMe 1TB', 'Lab Data Sains - Meja 2', 'ONLINE_LOCKED', true, '2026-10-31 12:48:08', '2026-11-01 14:32:39', NULL, NULL, NULL);
INSERT INTO public.computers VALUES (3, 'PC-03', 'Workstation Big Data & ML 03', 'AMD Ryzen 7 7700X • 32GB RAM • RTX 3060 Ti 8GB • NVMe 1TB', 'Lab Data Sains - Meja 3', 'ONLINE_LOCKED', true, '2026-10-31 12:48:08', '2026-11-01 14:32:39', NULL, NULL, NULL);
INSERT INTO public.computers VALUES (1, 'PC-01', 'Workstation AI & Deep Learning 01', 'Intel Core i7-13700K • 32GB RAM • RTX 4070 12GB • NVMe 1TB', 'Lab Data Sains - Meja 1', 'ONLINE_LOCKED', true, '2026-10-31 12:48:08', '2026-11-03 15:44:59', NULL, NULL, NULL);


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.migrations VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO public.migrations VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO public.migrations VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO public.migrations VALUES (4, '2026_10_31_000001_create_computers_table', 1);
INSERT INTO public.migrations VALUES (5, '2026_10_31_000002_create_reservations_table', 1);
INSERT INTO public.migrations VALUES (6, '2026_10_31_000003_create_access_codes_table', 1);
INSERT INTO public.migrations VALUES (7, '2026_10_31_000004_create_attendance_sessions_table', 1);
INSERT INTO public.migrations VALUES (8, '2026_11_01_000001_add_approved_by_to_reservations_table', 2);
INSERT INTO public.migrations VALUES (9, '2026_11_01_000002_add_maintenance_fields_to_computers_table', 3);
INSERT INTO public.migrations VALUES (10, '2026_11_02_000001_add_is_unlimited_session_to_users_table', 4);


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: reservations; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.reservations VALUES (1, 1, 1, '2026-11-03', '08:00:00', '11:00:00', '2026-11-03 08:00:00', '2026-11-03 11:00:00', 'Praktikum Mandiri & Coding Tugas Kuliah', 'jjj n', 'COMPLETED', NULL, '2026-11-01 15:05:35', '2026-11-01 15:12:28', 2, '2026-11-01 22:09:46');
INSERT INTO public.reservations VALUES (2, 1, 1, '2026-11-02', '08:00:00', '22:35:33', '2026-11-02 08:00:00', '2026-11-01 22:35:33', 'Proyek Sains Data & Machine Learning', NULL, 'COMPLETED', NULL, '2026-11-01 15:33:32', '2026-11-01 15:35:33', 2, '2026-11-01 22:33:51');
INSERT INTO public.reservations VALUES (5, 4, 2, '2026-11-10', '08:00:00', '11:00:00', '2026-11-10 08:00:00', '2026-11-10 11:00:00', 'Penelitian AI / Deep Learning GPU CUDA', NULL, 'APPROVED', NULL, '2026-11-02 20:57:55', '2026-11-02 20:58:28', 2, '2026-11-02 20:58:28');
INSERT INTO public.reservations VALUES (6, 4, 4, '2026-11-26', '08:00:00', '11:00:00', '2026-11-26 08:00:00', '2026-11-26 11:00:00', 'Proyek Sains Data & Machine Learning', NULL, 'APPROVED', NULL, '2026-11-02 21:29:32', '2026-11-02 21:30:09', 2, '2026-11-02 21:30:09');
INSERT INTO public.reservations VALUES (3, 1, 1, '2026-11-02', '08:00:00', '11:00:00', '2026-11-02 08:00:00', '2026-11-02 11:00:00', 'Praktikum Mandiri & Coding Tugas Kuliah', NULL, 'CANCELLED_NO_SHOW', NULL, '2026-11-01 15:54:32', '2026-11-03 12:06:07', 2, '2026-11-01 22:54:38');
INSERT INTO public.reservations VALUES (4, 1, 1, '2026-11-02', '11:30:00', '16:00:00', '2026-11-02 11:30:00', '2026-11-02 16:00:00', 'Proyek Sains Data & Machine Learning', NULL, 'CANCELLED_NO_SHOW', NULL, '2026-11-02 11:22:10', '2026-11-03 12:06:07', 2, '2026-11-02 11:23:02');


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.users VALUES (3, 'Siti Rahmawati', '22081010099', 'Teknik Informatika', 'siti@uinbanten.ac.id', NULL, '$2y$12$OULxHHQbVljTiL0pUjRClOhJ60GVDfr2q1XnvequM3gVncMQolyz6', 'mahasiswa', true, NULL, '2026-10-31 15:21:24', '2026-11-02 11:40:34', false);
INSERT INTO public.users VALUES (4, 'Budi Santoso (VIP Unlimited)', '22081010888', 'Teknik Informatika', 'unlimited@uinbanten.ac.id', NULL, '$2y$12$eemcg9yuuliqZFmgCmIbbekGU8U5baE50JFMcVJG5ompn6bWABw1W', 'mahasiswa', true, NULL, '2026-11-02 11:40:34', '2026-11-02 11:40:34', true);
INSERT INTO public.users VALUES (1, 'M. Subagja', '22081010045', 'Teknik Informatika', 'subagja@uinbanten.ac.id', NULL, '$2y$12$hEIUmp4Eoo7ZKpA19xwSS.MsDvfp4oeTm71ZQpEZZkqo0ESVK54Ti', 'mahasiswa', true, 'nPjZP6BmaikuTVjGEUmAg2e14dsHyIf8CssJgHdUSBMqdc2pRhbdZrbnbdSQ', '2026-10-31 12:48:07', '2026-11-02 11:40:34', false);
INSERT INTO public.users VALUES (2, 'Kepala Laboratorium', 'kepala_lab', 'Laboratorium Sains Data & AI', 'kepalalab@uinbanten.ac.id', NULL, '$2y$12$GyU59cDdv3AfBXbFFXcmyeUOwT9cdUjNHXAndWQ7hyLUiwbO8H9JS', 'kepala_lab', true, 'inh3fCKGpuuWXEMdWAlBtNqzjxlnWaObCkTneTNRYABewFWz7cQkobZuhZAS', '2026-10-31 12:48:08', '2026-11-03 15:23:16', false);


--
-- Name: access_codes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.access_codes_id_seq', 6, true);


--
-- Name: attendance_sessions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.attendance_sessions_id_seq', 2, true);


--
-- Name: computers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.computers_id_seq', 4, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 10, true);


--
-- Name: reservations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.reservations_id_seq', 7, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 4, true);


--
-- Name: access_codes access_codes_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.access_codes
    ADD CONSTRAINT access_codes_code_unique UNIQUE (code);


--
-- Name: access_codes access_codes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.access_codes
    ADD CONSTRAINT access_codes_pkey PRIMARY KEY (id);


--
-- Name: attendance_sessions attendance_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attendance_sessions
    ADD CONSTRAINT attendance_sessions_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: computers computers_computer_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.computers
    ADD CONSTRAINT computers_computer_code_unique UNIQUE (computer_code);


--
-- Name: computers computers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.computers
    ADD CONSTRAINT computers_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: reservations reservations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_nim_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_nim_unique UNIQUE (nim);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: access_codes access_codes_reservation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.access_codes
    ADD CONSTRAINT access_codes_reservation_id_foreign FOREIGN KEY (reservation_id) REFERENCES public.reservations(id) ON DELETE CASCADE;


--
-- Name: attendance_sessions attendance_sessions_computer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attendance_sessions
    ADD CONSTRAINT attendance_sessions_computer_id_foreign FOREIGN KEY (computer_id) REFERENCES public.computers(id) ON DELETE CASCADE;


--
-- Name: attendance_sessions attendance_sessions_reservation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attendance_sessions
    ADD CONSTRAINT attendance_sessions_reservation_id_foreign FOREIGN KEY (reservation_id) REFERENCES public.reservations(id) ON DELETE CASCADE;


--
-- Name: attendance_sessions attendance_sessions_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attendance_sessions
    ADD CONSTRAINT attendance_sessions_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: reservations reservations_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: reservations reservations_computer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_computer_id_foreign FOREIGN KEY (computer_id) REFERENCES public.computers(id) ON DELETE CASCADE;


--
-- Name: reservations reservations_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reservations
    ADD CONSTRAINT reservations_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict 7YzTrXEPHHuKp3UO25GAsIeJY0Po7rg2lnsvFOYtBuqobxA8mxDdCr9t8gpppQ3

